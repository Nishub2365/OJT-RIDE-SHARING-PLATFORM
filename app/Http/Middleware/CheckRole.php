<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('auth.phone');
        }

        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            return redirect()->route('home')->withErrors(['banned' => 'Your account has been suspended. Please contact support.']);
        }

        // Admin can access all
        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role !== $role) {
            return match ($user->role) {
                'rider'  => redirect()->route('rider.dashboard'),
                'driver' => redirect()->route('driver.dashboard'),
                'admin'  => redirect()->route('admin.dashboard'),
                default  => redirect()->route('auth.phone')
                    ->withErrors(['role' => 'Your account role is invalid. Please contact support.']),
            };
        }

        // Driver must have approved profile to access driver pages (except pending/documents/profile)
        if ($role === 'driver') {
            $allowedPending = ['driver/pending', 'driver/documents', 'driver/profile', 'auth/logout'];
            $currentPath    = $request->path();
            $dp             = $user->driverProfile;
            $isApproved     = $dp && $dp->status === 'approved';

            if (!$isApproved && !collect($allowedPending)->contains(fn($p) => str_starts_with($currentPath, $p))) {
                return redirect()->route('driver.pending')
                    ->with('flash_info', 'Please wait for your account to be approved.');
            }
        }

        return $next($request);
    }
}