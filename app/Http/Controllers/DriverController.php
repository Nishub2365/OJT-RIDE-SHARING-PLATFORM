<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use App\Models\DriverProfile;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\RideBid;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────
    public function dashboard()
    {
        $user    = Auth::user();
        $profile = $user->driverProfile;

        $activeRide = Ride::where('driver_id', $user->id)
            ->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])
            ->latest()->first();

        $todayEarnings = Ride::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->sum('agreed_fare');

        $weekEarnings = Ride::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('agreed_fare');

        $monthEarnings = Ride::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->sum('agreed_fare');

        // Pending rides driver can bid on
        $pendingRides = Ride::where('status', 'pending')
            ->whereNull('driver_id')
            ->whereDoesntHave('bids', fn($q) => $q->where('driver_id', $user->id))
            ->when($profile?->vehicle_type, fn($q, $v) => $q->where('vehicle_category', $v))
            ->orderByDesc('created_at')
            ->take(10)->get();

        // 7-day chart data
        $chartLabels = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('D'))->values();
        $chartData   = collect(range(6, 0))->map(fn($d) =>
            Ride::where('driver_id', $user->id)->where('status', 'completed')
                ->whereDate('updated_at', now()->subDays($d))->sum('agreed_fare')
        )->values();

        return view('driver.dashboard', compact(
            'user', 'profile', 'activeRide', 'todayEarnings', 'weekEarnings',
            'monthEarnings', 'pendingRides', 'chartLabels', 'chartData'
        ));
    }

    // ── Toggle online status ─────────────────────────────────────────
    public function toggleOnline()
    {
        $profile = Auth::user()->driverProfile;
        if ($profile) {
            $profile->update(['is_online' => ! $profile->is_online]);
        }
        return back();
    }

    // ── Active ride ──────────────────────────────────────────────────
    public function activeRide($rideId = null)
    {
        $user = Auth::user();

        if ($rideId) {
            $ride = Ride::with(['rider', 'ratings'])->where('driver_id', $user->id)->findOrFail($rideId);
        } else {
            $ride = Ride::with(['rider', 'ratings'])->where('driver_id', $user->id)
                ->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])
                ->latest()->first();
        }

        return view('driver.active-ride', compact('ride'));
    }

    // ── Update ride status ───────────────────────────────────────────
    public function updateRideStatus(Request $request, $rideId)
    {
        $request->validate(['status' => 'required|in:driver_arrived,in_progress,completed,cancelled']);

        $ride = Ride::where('driver_id', Auth::id())
            ->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])
            ->findOrFail($rideId);

        DB::transaction(function () use ($ride, $request) {
            $update = ['status' => $request->status];

            if ($request->status === 'completed') {
                $update['completed_at'] = now();
                $fare = $ride->agreed_fare ?? $ride->offered_fare;

                // Credit driver wallet
                Auth::user()->increment('wallet_balance', $fare);
                Auth::user()->driverProfile?->increment('total_trips');
                Auth::user()->driverProfile?->increment('total_earned', $fare);

                WalletTransaction::create([
                    'user_id'     => Auth::id(),
                    'type'        => 'credit',
                    'amount'      => $fare,
                    'description' => "Ride #$ride->id fare",
                    'reference_id'=> $ride->id,
                ]);

                // Deduct from rider wallet if wallet payment
                if ($ride->payment_method === 'wallet') {
                    $rider = $ride->rider;
                    $rider->decrement('wallet_balance', $fare);
                    WalletTransaction::create([
                        'user_id'     => $rider->id,
                        'type'        => 'debit',
                        'amount'      => $fare,
                        'description' => "Ride #$ride->id fare",
                        'reference_id'=> $ride->id,
                    ]);
                }
            }

            if ($request->status === 'cancelled') {
                $update['cancelled_by'] = 'driver';
            }

            $ride->update($update);
        });

        $msg = match($request->status) {
            'driver_arrived' => 'Marked as arrived at pickup point.',
            'in_progress'    => 'Ride started!',
            'completed'      => 'Ride completed! Fare credited to your wallet.',
            'cancelled'      => 'Ride cancelled.',
            default          => 'Status updated.',
        };

        return redirect()->route('driver.active-ride', $rideId)->with('flash_success', $msg);
    }

    // ── Place bid on a ride ──────────────────────────────────────────
    public function placeBid(Request $request, $rideId)
    {
        $request->validate(['bid_amount' => 'required|numeric|min:50']);

        $ride = Ride::where('status', 'pending')->findOrFail($rideId);

        // Check driver hasn't already bid
        $exists = RideBid::where('ride_id', $rideId)->where('driver_id', Auth::id())->exists();
        if ($exists) {
            return back()->withErrors(['bid' => 'You have already placed a bid on this ride.']);
        }

        RideBid::create([
            'ride_id'    => $rideId,
            'driver_id'  => Auth::id(),
            'bid_amount' => $request->bid_amount,
            'message'    => $request->message,
            'status'     => 'pending',
        ]);

        return back()->with('flash_success', 'Bid placed! Waiting for rider to accept.');
    }

    // ── Earnings ─────────────────────────────────────────────────────
    public function earnings()
    {
        $user = Auth::user();

        $today    = Ride::where('driver_id', $user->id)->where('status','completed')->whereDate('updated_at', today())->sum('agreed_fare');
        $week     = Ride::where('driver_id', $user->id)->where('status','completed')->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('agreed_fare');
        $month    = Ride::where('driver_id', $user->id)->where('status','completed')->whereMonth('updated_at', now()->month)->sum('agreed_fare');
        $allTime  = Ride::where('driver_id', $user->id)->where('status','completed')->sum('agreed_fare');

        $completedRides = Ride::where('driver_id', $user->id)->where('status','completed')
            ->with('rider')->orderByDesc('updated_at')->paginate(15);

        $chartLabels = collect(range(29,0))->map(fn($d) => now()->subDays($d)->format('d/m'))->values();
        $chartData   = collect(range(29,0))->map(fn($d) =>
            Ride::where('driver_id',$user->id)->where('status','completed')
                ->whereDate('updated_at', now()->subDays($d))->sum('agreed_fare')
        )->values();

        return view('driver.earnings', compact('today','week','month','allTime','completedRides','chartLabels','chartData'));
    }

    // ── Documents ────────────────────────────────────────────────────
    public function documents()
    {
        $user = Auth::user()->load('documents');
        return view('driver.documents', compact('user'));
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'doc_type' => 'required|in:citizenship,driving_license,vehicle_registration,vehicle_photo',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $user = Auth::user();
        $path = $request->file('document')->store('driver-docs', 'public');

        DriverDocument::updateOrCreate(
            ['user_id' => $user->id, 'doc_type' => $request->doc_type],
            ['doc_path' => $path, 'status' => 'pending', 'rejection_reason' => null]
        );

        return back()->with('flash_success', ucwords(str_replace('_', ' ', $request->doc_type)) . ' uploaded! Under review.');
    }

    // ── Profile ──────────────────────────────────────────────────────
    public function profile()
    {
        $user = Auth::user()->load('driverProfile');
        return view('driver.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name'     => 'required|string|max:60',
            'last_name'      => 'required|string|max:60',
            'email'          => 'nullable|email|unique:users,email,' . $user->id,
            'avatar'         => 'nullable|image|max:2048',
            'vehicle_type'   => 'nullable|in:bike,auto,car,suv,truck',
            'vehicle_model'  => 'nullable|string|max:80',
            'vehicle_number' => 'nullable|string|max:20',
            'vehicle_color'  => 'nullable|string|max:30',
        ]);

        $data = ['first_name' => $request->first_name, 'last_name' => $request->last_name, 'email' => $request->email];
        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) Storage::disk('public')->delete($user->avatar_url);
            $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);

        $user->driverProfile?->update([
            'vehicle_type'   => $request->vehicle_type,
            'vehicle_model'  => $request->vehicle_model,
            'vehicle_number' => $request->vehicle_number ? strtoupper($request->vehicle_number) : null,
            'vehicle_color'  => $request->vehicle_color,
        ]);

        return back()->with('flash_success', 'Profile updated!');
    }

    // ── Update live location (AJAX) ──────────────────────────────────
    public function updateLocation(Request $request)
    {
        $request->validate(['lat' => 'required|numeric', 'lng' => 'required|numeric']);

        Auth::user()->driverProfile?->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Bid history ──────────────────────────────────────────────────
    public function requests()
    {
        $bids = RideBid::where('driver_id', Auth::id())
            ->with('ride.rider')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('driver.requests', compact('bids'));
    }

    // ── Pending page ─────────────────────────────────────────────────
    public function pending()
    {
        $user = Auth::user()->load('driverProfile', 'documents');
        return view('driver.pending', compact('user'));
    }

    // ── Rate rider ───────────────────────────────────────────────────
    public function showRate($rideId)
    {
        $ride = Ride::with('rider')->where('driver_id', Auth::id())
            ->where('status', 'completed')->findOrFail($rideId);

        $alreadyRated = Rating::where('rater_id', Auth::id())->where('ride_id', $rideId)->exists();
        if ($alreadyRated) {
            return redirect()->route('driver.dashboard')->with('flash_info', 'Already rated.');
        }

        return view('driver.rate', compact('ride'));
    }

    public function storeRate(Request $request, $rideId)
    {
        $request->validate(['rating' => 'required|integer|between:1,5']);

        $ride = Ride::where('driver_id', Auth::id())->where('status', 'completed')->findOrFail($rideId);

        Rating::updateOrCreate(
            ['rater_id' => Auth::id(), 'ride_id' => $rideId],
            [
                'ratee_id'   => $ride->rider_id,
                'rating'     => $request->rating,
                'comment'    => $request->comment,
                'rater_type' => 'driver',
            ]
        );

        $avg = Rating::where('ratee_id', $ride->rider_id)->avg('rating');
        $ride->rider->update(['avg_rating' => round($avg, 2)]);

        return redirect()->route('driver.dashboard')->with('flash_success', 'Thanks for rating your rider!');
    }
}
