<?php
namespace App\Http\Controllers;
use App\Models\AppNotification;
use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SosController extends Controller
{
    public function trigger(Request $request)
    {
        $sos = SosAlert::create([
            'user_id' => Auth::id(),
            'ride_id' => $request->ride_id,
            'lat'     => $request->lat,
            'lng'     => $request->lng,
            'status'  => 'active',
        ]);

        // Notify all admins
        User::where('role', 'admin')->each(function ($admin) use ($sos) {
            AppNotification::create([
                'user_id' => $admin->id,
                'title'   => '🆘 SOS ALERT',
                'body'    => Auth::user()->full_name . ' needs help!' .
                    ($sos->lat ? " GPS: {$sos->lat},{$sos->lng}" : ' No GPS'),
                'type'    => 'sos',
            ]);
        });

        return response()->json(['ok' => true, 'id' => $sos->id]);
    }
}
