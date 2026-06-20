<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\PasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Show phone + role form
    public function showPhone()
    {
        return view('auth.phone');
    }

    // Submit phone + password — log in if credentials match, else go to register
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone'    => 'required|digits:10',
            'role'     => 'required|in:rider,driver,admin',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        // No account with this phone yet — send to registration
        if (! $user) {
            session([
                'otp_phone' => $request->phone,
                'otp_role'  => $request->role,
            ]);

            return redirect()->route('auth.register');
        }

        // Check ban FIRST before anything else
        if ($user->is_banned) {
            throw ValidationException::withMessages([
                'phone' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        // Account exists but has no password set (legacy/seeded phone-only user)
        if (! $user->password) {
            throw ValidationException::withMessages([
                'password' => 'This account has no password set yet. Please use "Forgot Password" to set one.',
            ]);
        }

        // Wrong password
        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The phone number or password is incorrect.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    // Show registration form
    public function showRegister()
    {
        if (! session('otp_phone')) {
            return redirect()->route('auth.phone');
        }
        return view('auth.register');
    }

    // Create account
    public function register(Request $request)
    {
        $request->validate([
            'first_name'     => 'required|string|max:60',
            'last_name'      => 'required|string|max:60',
            'email'          => 'nullable|email|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'avatar'         => 'nullable|image|max:2048',
            'agree_terms'    => 'required',
            'vehicle_type'   => 'required_if:role,driver|in:bike,auto,car,suv,truck',
            'vehicle_model'  => 'required_if:role,driver|string|max:80',
            'vehicle_number' => 'required_if:role,driver|string|max:20',
            'vehicle_color'  => 'nullable|string|max:30',
        ]);

        $phone = session('otp_phone');
        $role  = session('otp_role', 'rider');

        if (! $phone) {
            return redirect()->route('auth.phone');
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'phone'          => $phone,
            'email'          => $request->email,
            'password'       => $request->password, // hashed automatically via the 'hashed' cast on User
            'role'           => $role,
            'avatar_url'     => $avatarPath,
            'wallet_balance' => 0,
        ]);

        if ($role === 'driver') {
            DriverProfile::create([
                'user_id'        => $user->id,
                'vehicle_type'   => $request->vehicle_type,
                'vehicle_model'  => $request->vehicle_model,
                'vehicle_number' => strtoupper($request->vehicle_number),
                'vehicle_color'  => $request->vehicle_color,
                'status'         => 'pending',
                'is_online'      => false,
                'total_trips'    => 0,
                'total_earned'   => 0,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        session()->forget(['otp_phone', 'otp_role']);

        return $this->redirectByRole($user)
            ->with('flash_success', 'Welcome to BROCAR, ' . $user->first_name . '!');
    }

    // Show "forgot password" form
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Submit a forgot-password request — queued for admin approval, not an instant reset
    public function submitForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'email' => 'nullable|email',
        ]);

        if (! User::where('phone', $request->phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'No account was found with this phone number.',
            ]);
        }

        PasswordRequest::create([
            'phone'  => $request->phone,
            'email'  => $request->email,
            'status' => 'pending',
        ]);

        return redirect()->route('auth.phone')
            ->with('flash_success', 'Your request has been sent. An admin will review it shortly.');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    protected function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'driver' => redirect()->route('driver.dashboard'),
            'rider'  => redirect()->route('rider.dashboard'),
            default  => redirect()->route('auth.phone')
                ->withErrors(['role' => 'Your account role is invalid. Please contact support.']),
        };
    }
}