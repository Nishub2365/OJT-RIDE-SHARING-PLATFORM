<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Complaint;
use App\Models\PasswordRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\DriverDocument;
use App\Models\DriverProfile;
use App\Models\PromoCode;
use App\Models\Ride;
use App\Models\SosAlert;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ── Dashboard ────────────────────────────────────────────────────
    public function dashboard()
    {
        $today = Carbon::today();

        $stats = [
            'total_users'     => User::where('role', '!=', 'admin')->count(),
            'active_drivers'  => DriverProfile::where('status', 'approved')->where('is_online', true)->count(),
            'rides_today'     => Ride::whereDate('created_at', $today)->count(),
            'revenue_today'   => Ride::where('status', 'completed')->whereDate('updated_at', $today)->sum('agreed_fare'),
            'completed'       => Ride::where('status', 'completed')->count(),
            'cancelled'       => Ride::where('status', 'cancelled')->count(),
            'pending_drivers' => DriverProfile::where('status', 'pending')->count(),
            'open_sos'        => SosAlert::where('status', 'active')->count(),
        ];

        // Optimized: Generate day offsets cleanly to avoid multiple expensive relative carbon mutations
        $rideLabels = collect(range(6, 0))->map(fn($d) => Carbon::now()->subDays($d)->format('D d'));
        
        $rideData   = collect(range(6, 0))->map(fn($d) => Ride::whereDate('created_at', Carbon::now()->subDays($d))->count());
        $revLabels  = $rideLabels;
        $revData    = collect(range(6, 0))->map(fn($d) =>
            Ride::where('status', 'completed')->whereDate('updated_at', Carbon::now()->subDays($d))->sum('agreed_fare')
        );

        // Fix: Avoid N+1 queries by eager-loading profile indicators or names where relevant
        $recentRides = Ride::with(['rider', 'driver'])->orderByDesc('created_at')->take(8)->get();
        $sosList     = SosAlert::with('user')->where('status', 'active')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'rideLabels', 'rideData', 'revLabels', 'revData', 'recentRides', 'sosList'));
    }

    // ── Drivers ──────────────────────────────────────────────────────
    public function drivers(Request $request)
    {
        $query = User::where('role', 'driver')->with('driverProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
            );
        }
        
        if ($request->filled('status')) {
            $query->whereHas('driverProfile', fn($q) => $q->where('status', $request->status));
        }

        $drivers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.drivers', compact('drivers'));
    }

    public function driverDetail($id)
    {
        // Fix: Prevent relationship chain crashes by ensuring deep relationships are correctly queried
        $driver = User::where('role', 'driver')->with(['driverProfile', 'documents', 'ridesAsDriver'])->findOrFail($id);
        return view('admin.driver-detail', compact('driver'));
    }

    public function approveDriver($id)
    {
        $driver = User::findOrFail($id);
        
        // Fix: Ensure the driver profile record actually exists before updating it
        $profile = DriverProfile::firstOrCreate(['user_id' => $driver->id]);
        $profile->update(['status' => 'approved']);

        AppNotification::create([
            'user_id' => $driver->id,
            'title'   => '🎉 Account Approved!',
            'body'    => 'Your driver account has been approved. You can now go online and accept rides.',
            'type'    => 'system',
        ]);

        // Fix: Changed flash key to match app layout notification component rules
        return back()->with('flash_success', "Driver approved successfully.");
    }

    public function rejectDriver(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'nullable|string|max:255']);
        $driver = User::findOrFail($id);
        
        $profile = DriverProfile::firstOrCreate(['user_id' => $driver->id]);
        $profile->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason ?? 'Documents not meeting requirements',
        ]);

        AppNotification::create([
            'user_id' => $driver->id,
            'title'   => '❌ Account Rejected',
            'body'    => 'Your driver application was not approved. Please re-upload your documents.',
            'type'    => 'system',
        ]);

        return back()->with('flash_success', "Driver rejected.");
    }

    public function suspendDriver($id)
    {
        $driver = User::findOrFail($id);
        $profile = DriverProfile::firstOrCreate(['user_id' => $driver->id]);
        $profile->update(['status' => 'suspended', 'is_online' => false]);
        
        return back()->with('flash_success', "Driver suspended.");
    }

    public function verifyDocument(Request $request, $docId)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'reason' => 'required_if:status,rejected|nullable|string|max:255'
        ]);
        
        $doc = DriverDocument::findOrFail($docId);
        $doc->update([
            'status'           => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? ($request->reason ?? 'Document unclear') : null,
        ]);
        return back()->with('flash_success', 'Document status updated to ' . $request->status . '.');
    }

    // ── Users ─────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'admin');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
            );
        }
        
        $users = $query->withCount(['ridesAsRider as rides_count'])->orderByDesc('created_at')->paginate(25)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => true]);
        return back()->with('flash_success', 'User banned.');
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => false]);
        return back()->with('flash_success', 'User unbanned.');
    }

    // ── Rides ─────────────────────────────────────────────────────────
    public function rides(Request $request)
    {
        $query = Ride::with(['rider', 'driver']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            // Fix: Fixed syntax layout by using a normal closure structure instead of an invalid PHP arrow function format
            $query->where(function($mainQ) use ($search) {
                $mainQ->whereHas('rider', fn($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                      ->orWhereHas('driver', fn($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }
        
        $rides = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        return view('admin.rides', compact('rides'));
    }

    // ── SOS ───────────────────────────────────────────────────────────
    public function sos()
    {
        $sosList = SosAlert::with('user')->orderByDesc('created_at')->paginate(25);
        return view('admin.sos', compact('sosList'));
    }

    public function resolveSos($id)
    {
        SosAlert::findOrFail($id)->update(['status' => 'resolved', 'resolved_at' => Carbon::now()]);
        return back()->with('flash_success', 'SOS resolved.');
    }

    // ── Analytics ─────────────────────────────────────────────────────
    public function analytics()
    {
        $totalRidesCount = Ride::count();

        $analytics = [
            'total_revenue'   => Ride::where('status', 'completed')->sum('agreed_fare'),
            'completed_rides' => Ride::where('status', 'completed')->count(),
            'cancel_rate'     => $totalRidesCount > 0 ? (Ride::where('status', 'cancelled')->count() / $totalRidesCount) * 100 : 0,
            'avg_fare'        => Ride::where('status', 'completed')->avg('agreed_fare') ?? 0,
        ];

        $rideLabels    = collect(range(29, 0))->map(fn($d) => Carbon::now()->subDays($d)->format('d/m'));
        $rideData      = collect(range(29, 0))->map(fn($d) => Ride::whereDate('created_at', Carbon::now()->subDays($d))->count());
        $revenueLabels = $rideLabels;
        $revenueData   = collect(range(29, 0))->map(fn($d) =>
            Ride::where('status', 'completed')->whereDate('updated_at', Carbon::now()->subDays($d))->sum('agreed_fare')
        );

        $vehicleData = collect(['bike', 'auto', 'car', 'suv'])->map(fn($v) =>
            Ride::where('vehicle_category', $v)->count()
        );

        // Fix: Replaced raw MySQL-only queries with agnostic Subqueries to allow error-free SQLite execution
        $topDrivers = User::where('role', 'driver')
            ->with('driverProfile')
            ->addSelect(['total_earned' => Ride::selectRaw('SUM(agreed_fare)')
                ->whereColumn('driver_id', 'users.id')
                ->where('status', 'completed')
            ])
            ->addSelect(['avg_rating' => DB::table('ratings')->selectRaw('AVG(rating)')
                ->whereColumn('ratee_id', 'users.id')
            ])
            ->orderByDesc('total_earned')
            ->take(10)->get();

        return view('admin.analytics', compact('analytics', 'rideLabels', 'rideData', 'revenueLabels', 'revenueData', 'vehicleData', 'topDrivers'));
    }

    // ── Finance ───────────────────────────────────────────────────────
    public function finance()
    {
        $finance = [
            'total_revenue' => Ride::where('status', 'completed')->sum('agreed_fare'),
            'platform_fees' => Ride::where('status', 'completed')->sum('agreed_fare') * 0.10,
            'wallet_total'  => User::sum('wallet_balance'),
            'withdrawals'   => WalletTransaction::where('type', 'debit')->where('description', 'like', '%withdraw%')->sum('amount'),
        ];
        $transactions = WalletTransaction::with('user')->orderByDesc('created_at')->paginate(25);
        return view('admin.finance', compact('finance', 'transactions'));
    }

    // ── Tickets ───────────────────────────────────────────────────────
    public function tickets(Request $request)
    {
        $query = SupportTicket::with('user');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $tickets = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.tickets', compact('tickets'));
    }

    public function replyTicket(Request $request, $id)
    {
        $request->validate(['reply' => 'required|string|max:1000', 'status' => 'required|string|in:open,pending,resolved,closed']);
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['admin_reply' => $request->reply, 'status' => $request->status]);
        
        AppNotification::create([
            'user_id' => $ticket->user_id,
            'title'   => 'Support reply',
            'body'    => "Your ticket #{$id} has been updated.",
            'type'    => 'support',
        ]);
        return back()->with('flash_success', 'Reply sent.');
    }

    // ── Promos ────────────────────────────────────────────────────────
    public function promos()
    {
        $promos = PromoCode::orderByDesc('created_at')->get();
        return view('admin.promos', compact('promos'));
    }

    public function storePromo(Request $request)
    {
        $request->validate([
            'code'           => 'required|string|max:50|unique:promo_codes,code',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:1',
            'max_uses'       => 'required|integer|min:1',
            'expires_at'     => 'required|date|after:today',
            'min_fare'       => 'nullable|numeric|min:0',
            'max_discount'   => 'nullable|numeric|min:0',
            'starts_at'      => 'nullable|date',
        ]);

        PromoCode::create([
            'code'           => strtoupper($request->code),
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'max_uses'       => $request->max_uses,
            'uses_count'     => 0,
            'min_fare'       => $request->min_fare ?? 0,
            'max_discount'   => $request->max_discount,
            'starts_at'      => $request->starts_at ?? Carbon::now(),
            'expires_at'     => $request->expires_at,
            'is_active'      => true,
        ]);

        return back()->with('flash_success', 'Promo code created!');
    }

    public function togglePromo($id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->update(['is_active' => !$promo->is_active]);
        return back()->with('flash_success', 'Promo ' . ($promo->is_active ? 'enabled' : 'disabled') . '.');
    }

    // ── Broadcast ─────────────────────────────────────────────────────
    public function broadcast(Request $request)
    {
        $request->validate(['message' => 'required|string|max:300']);

        // Fix: Replaced Memory-heavy full collection loop with a chunked cursor query
        User::select('id')->chunk(200, function ($users) use ($request) {
            foreach ($users as $user) {
                AppNotification::create([
                    'user_id' => $user->id,
                    'title'   => '📢 Broadcast',
                    'body'    => $request->message,
                    'type'    => 'broadcast',
                ]);
            }
        });

        return back()->with('flash_success', "Broadcast sent to all users.");
    }

    // ── Complaints ────────────────────────────────────────────────────
    public function complaints()
    {
        $complaints = Complaint::with(['complainant', 'accused'])->orderByDesc('created_at')->paginate(20);
        return view('admin.complaints', compact('complaints'));
    }

    public function resolveComplaint($id)
    {
        Complaint::findOrFail($id)->update(['status' => 'resolved']);
        return back()->with('flash_success', 'Complaint resolved.');
    }

    // ── Password Requests (Forgot Password) ───────────────────────────
    // Fix: Moved these functions back inside the AdminController class boundary scope
    public function passwordRequests()
    {
        $requests = PasswordRequest::orderByDesc('created_at')->paginate(20);
        return view('admin.password-requests.blade', compact('requests'));
    }

    public function resolvePasswordRequest(Request $request, $id)
    {
        $request->validate([
            'action'     => 'required|in:approved,rejected',
            'new_password' => 'required_if:action,approved|nullable|string|min:6',
            'admin_note' => 'nullable|string|max:300',
        ]);

        $pr = PasswordRequest::findOrFail($id);

        if ($request->action === 'approved' && $request->filled('new_password')) {
            // Reset the user's password
            $user = User::where('phone', $pr->phone)->first();
            if ($user) {
                $user->update(['password' => Hash::make($request->new_password)]);
            }
        }

        $pr->update([
            'status'      => $request->action,
            'admin_note'  => $request->admin_note,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('flash_success',
            $request->action === 'approved'
                ? "Password reset for {$pr->phone} ✅"
                : "Request rejected for {$pr->phone}."
        );
    }
}