<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return match(Auth::user()->role) {
                'admin'  => redirect()->route('admin.dashboard'),
                'driver' => redirect()->route('driver.dashboard'),
                default  => redirect()->route('rider.dashboard'),
            };
        }
        return view('home');
    }
}
