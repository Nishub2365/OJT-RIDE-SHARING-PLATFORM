<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\DeliveryOrder;
use App\Models\PromoCode;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\RideBid;
use App\Models\SavedLocation;
use App\Models\TrustedContact;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiderController extends Controller
{
    // ── Dashboard ────────────────────────────────────────────────────
    public function dashboard()
    {
        $user = Auth::user();

        $activeRide = Ride::where('rider_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()->first();

        $totalRides  = Ride::where('rider_id', $user->id)->count();
        $monthRides  = Ride::where('rider_id', $user->id)
            ->whereMonth('created_at', now()->month)->count();
        $recentRides = Ride::where('rider_id', $user->id)
            ->with('driver')
            ->orderByDesc('created_at')
            ->take(8)->get();

        return view('rider.dashboard', compact('user', 'activeRide', 'totalRides', 'monthRides', 'recentRides'));
    }

    // ── Request ride form ────────────────────────────────────────────
    public function requestRide()
    {
        return view('rider.request-ride');
    }

    // ── Store new ride request ───────────────────────────────────────
    public function storeRide(Request $request)
    {
        $request->validate([
            'pickup_address'      => 'required|string',
            'destination_address' => 'required|string',
            'vehicle_category'    => 'required|in:bike,auto,car,suv,truck',
            'offered_fare'        => 'required|numeric|min:50',
            'payment_method'      => 'required|in:cash,wallet',
        ]);

        $user = Auth::user();

        // Check wallet balance if paying by wallet
        if ($request->payment_method === 'wallet') {
            $fare = $request->offered_fare;
            // Apply promo
            if ($request->promo_code) {
                $promo = PromoCode::where('code', strtoupper($request->promo_code))
                    ->where('is_active', true)->first();
                if ($promo && $promo->isValid($fare)) {
                    $discount = $promo->calculate($fare);
                    $fare = max(0, $fare - $discount);
                }
            }
            if ($user->wallet_balance < $fare) {
                return back()->withErrors(['payment_method' => 'Insufficient wallet balance. Please top up or pay by cash.']);
            }
        }

        $ride = Ride::create([
            'rider_id'            => $user->id,
            'pickup_address'      => $request->pickup_address,
            'pickup_lat'          => $request->pickup_lat,
            'pickup_lng'          => $request->pickup_lng,
            'destination_address' => $request->destination_address,
            'destination_lat'     => $request->destination_lat,
            'destination_lng'     => $request->destination_lng,
            'vehicle_category'    => $request->vehicle_category,
            'offered_fare'        => $request->offered_fare,
            'payment_method'      => $request->payment_method,
            'promo_code'          => $request->promo_code,
            'scheduled_at'        => $request->scheduled_at ?: null,
            'status'              => 'pending',
        ]);

        return redirect()->route('rider.active-ride', $ride->id)
            ->with('flash_success', 'Ride requested! Waiting for driver bids.');
    }

    // ── View active ride ─────────────────────────────────────────────
    public function activeRide($rideId = null)
    {
        $user = Auth::user();

        if ($rideId) {
            $ride = Ride::with(['driver', 'driver.driverProfile', 'bids.driver', 'bids.driver.driverProfile', 'ratings'])
                ->where('rider_id', $user->id)->findOrFail($rideId);
        } else {
            $ride = Ride::with(['driver', 'driver.driverProfile', 'bids.driver', 'bids.driver.driverProfile', 'ratings'])
                ->where('rider_id', $user->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest()->first();
        }

        return view('rider.active-ride', compact('ride'));
    }

    // ── Accept a bid ─────────────────────────────────────────────────
    public function acceptBid(Request $request, $rideId, $bidId)
    {
        $ride = Ride::where('rider_id', Auth::id())->where('status', 'pending')->findOrFail($rideId);
        $bid  = RideBid::where('ride_id', $rideId)->where('status', 'pending')->findOrFail($bidId);

        DB::transaction(function () use ($ride, $bid) {
            $ride->update([
                'driver_id'   => $bid->driver_id,
                'agreed_fare' => $bid->bid_amount,
                'status'      => 'accepted',
            ]);
            $bid->update(['status' => 'accepted']);
            // Reject all other bids
            RideBid::where('ride_id', $ride->id)->where('id', '!=', $bid->id)
                ->update(['status' => 'rejected']);
        });

        return back()->with('flash_success', 'Driver accepted! They are on their way.');
    }

    // ── Cancel ride ──────────────────────────────────────────────────
    public function cancelRide($rideId)
    {
        $ride = Ride::where('rider_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted'])
            ->findOrFail($rideId);

        $ride->update(['status' => 'cancelled', 'cancelled_by' => 'rider']);

        return redirect()->route('rider.dashboard')
            ->with('flash_success', 'Ride cancelled.');
    }

    // ── Get driver location (AJAX) ───────────────────────────────────
    public function driverLocation($rideId)
    {
        $ride = Ride::where('rider_id', Auth::id())->findOrFail($rideId);
        $dp   = $ride->driver?->driverProfile;

        return response()->json([
            'lat' => $dp?->current_lat,
            'lng' => $dp?->current_lng,
        ]);
    }

    // ── Get bids (AJAX polling) ──────────────────────────────────────
    public function getBids(Request $request, $rideId)
    {
        $ride = Ride::with(['bids.driver.driverProfile'])->where('rider_id', Auth::id())->findOrFail($rideId);
        $bids = $ride->bids->where('status', 'pending')->sortBy('bid_amount');

        $html = view('rider.partials.bid-card', ['bids' => $bids, 'rideId' => $rideId])->render();

        return response()->json([
            'count' => $bids->count(),
            'html'  => $bids->isEmpty()
                ? '<p style="text-align:center;color:var(--text3);font-size:13px;padding:20px">Waiting for drivers to bid…</p>'
                : $bids->map(fn($bid) => view('rider.partials.bid-card', compact('bid', 'rideId'))->render())->implode('')
        ]);
    }

    // ── Trip history ─────────────────────────────────────────────────
    public function history()
    {
        $rides = Ride::where('rider_id', Auth::id())
            ->with(['driver', 'ratings'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('rider.history', compact('rides'));
    }

    // ── Rate driver ──────────────────────────────────────────────────
    public function showRate($rideId)
    {
        $ride = Ride::with('driver')->where('rider_id', Auth::id())
            ->where('status', 'completed')->findOrFail($rideId);

        $alreadyRated = Rating::where('rater_id', Auth::id())
            ->where('ride_id', $rideId)->exists();
        if ($alreadyRated) {
            return redirect()->route('rider.history')->with('flash_info', 'You already rated this ride.');
        }

        return view('rider.rate', compact('ride'));
    }

    public function storeRate(Request $request, $rideId)
    {
        $request->validate(['rating' => 'required|integer|between:1,5']);

        $ride = Ride::where('rider_id', Auth::id())->where('status', 'completed')->findOrFail($rideId);

        Rating::updateOrCreate(
            ['rater_id' => Auth::id(), 'ride_id' => $rideId],
            [
                'ratee_id'     => $ride->driver_id,
                'rating'       => $request->rating,
                'comment'      => $request->comment,
                'rater_type'   => 'rider',
            ]
        );

        // Update driver's average rating
        if ($ride->driver) {
            $avg = Rating::where('ratee_id', $ride->driver_id)->avg('rating');
            $ride->driver->update(['avg_rating' => round($avg, 2)]);
        }

        return redirect()->route('rider.history')
            ->with('flash_success', 'Thank you for rating your driver!');
    }

    // ── Profile ──────────────────────────────────────────────────────
    public function profile()
    {
        $user = Auth::user()->load('trustedContacts', 'savedLocations');
        return view('rider.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Add trusted contact
        if ($request->add_contact) {
            $request->validate([
                'contact_name'  => 'required|string|max:60',
                'contact_phone' => 'required|string|max:15',
            ]);
            if ($user->trustedContacts()->count() < 3) {
                TrustedContact::create([
                    'user_id' => $user->id,
                    'name'    => $request->contact_name,
                    'phone'   => $request->contact_phone,
                ]);
            }
            return back()->with('flash_success', 'Trusted contact added!');
        }

        // Add saved location
        if ($request->add_location) {
            $request->validate(['location_address' => 'required|string']);
            SavedLocation::create([
                'user_id' => $user->id,
                'label'   => $request->add_location,
                'address' => $request->location_address,
            ]);
            return back()->with('flash_success', 'Location saved!');
        }

        // Update profile
        $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'required|string|max:60',
            'email'      => 'nullable|email|unique:users,email,' . $user->id,
            'avatar'     => 'nullable|image|max:2048',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) Storage::disk('public')->delete($user->avatar_url);
            $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);
        return back()->with('flash_success', 'Profile updated!');
    }

    // ── Schedule ─────────────────────────────────────────────────────
    public function schedule()
    {
        $scheduled = Ride::where('rider_id', Auth::id())
            ->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->get();

        return view('rider.schedule', compact('scheduled'));
    }

    public function scheduleStore(Request $request)
    {
        $request->validate([
            'pickup_address'      => 'required|string',
            'destination_address' => 'required|string',
            'vehicle_category'    => 'required|in:bike,auto,car,suv',
            'offered_fare'        => 'required|numeric|min:50',
            'scheduled_at'        => 'required|date|after:+1 hour',
        ]);

        Ride::create([
            'rider_id'            => Auth::id(),
            'pickup_address'      => $request->pickup_address,
            'destination_address' => $request->destination_address,
            'vehicle_category'    => $request->vehicle_category,
            'offered_fare'        => $request->offered_fare,
            'payment_method'      => $request->payment_method ?? 'cash',
            'scheduled_at'        => $request->scheduled_at,
            'status'              => 'pending',
        ]);

        return back()->with('flash_success', 'Ride scheduled successfully!');
    }

    // ── Share trip ───────────────────────────────────────────────────
    public function shareTrip($rideId)
    {
        $ride = Ride::with('driver.driverProfile')->where('rider_id', Auth::id())->findOrFail($rideId);
        return view('rider.share-trip', compact('ride'));
    }

    // ── Get online drivers count by vehicle type (AJAX) ──────────────
    public function onlineDrivers(Request $request)
    {
        $type = $request->query('type');
        $allowed = ['bike','auto','car','suv','truck'];

        $query = \App\Models\DriverProfile::where('is_online', true)
            ->where('status', 'approved');

        if ($type && in_array($type, $allowed)) {
            $query->where('vehicle_type', $type);
        }

        $drivers = $query->with('user:id,first_name,last_name,avg_rating')
            ->get()
            ->map(fn($dp) => [
                'name'         => $dp->user?->first_name . ' ' . $dp->user?->last_name,
                'vehicle_type' => $dp->vehicle_type,
                'vehicle_model'=> $dp->vehicle_model,
                'vehicle_number'=> $dp->vehicle_number,
                'rating'       => $dp->user?->avg_rating,
                'total_trips'  => $dp->total_trips,
            ]);

        return response()->json([
            'count'   => $drivers->count(),
            'drivers' => $drivers,
        ]);
    }


    // ── Ride detail ──────────────────────────────────────────────────
    public function rideDetail($id)
    {
        $ride = Ride::with(['driver.driverProfile','bids.driver','ratings'])
            ->where('rider_id', Auth::id())
            ->findOrFail($id);
        return view('rider.ride-detail', compact('ride'));
    }

    // ── Edit ride form ───────────────────────────────────────────────
    public function editRide($id)
    {
        $ride = Ride::with('bids')
            ->where('rider_id', Auth::id())
            ->whereIn('status', ['pending','scheduled'])
            ->findOrFail($id);
        return view('rider.edit-ride', compact('ride'));
    }

    // ── Update ride ──────────────────────────────────────────────────
    public function updateRide(Request $request, $id)
    {
        $ride = Ride::where('rider_id', Auth::id())
            ->whereIn('status', ['pending','scheduled'])
            ->findOrFail($id);

        $request->validate([
            'vehicle_category'    => 'required|in:bike,auto,car,suv,truck',
            'offered_fare'        => 'required|numeric|min:50',
            'payment_method'      => 'required|in:cash,wallet',
        ]);

        $ride->update([
            'vehicle_category'    => $request->vehicle_category,
            'pickup_address'      => $request->pickup_address    ?: $ride->pickup_address,
            'pickup_lat'          => $request->pickup_lat        ?: $ride->pickup_lat,
            'pickup_lng'          => $request->pickup_lng        ?: $ride->pickup_lng,
            'destination_address' => $request->destination_address ?: $ride->destination_address,
            'destination_lat'     => $request->destination_lat   ?: $ride->destination_lat,
            'destination_lng'     => $request->destination_lng   ?: $ride->destination_lng,
            'offered_fare'        => $request->offered_fare,
            'payment_method'      => $request->payment_method,
            'promo_code'          => $request->promo_code ?: null,
            'scheduled_at'        => $request->scheduled_at ?: null,
        ]);

        return redirect()->route('rider.ride-detail', $ride->id)
            ->with('flash_success', 'Ride updated successfully!');
    }

}