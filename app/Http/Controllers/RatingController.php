<?php
namespace App\Http\Controllers;
use App\Models\Rating;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, $rideId)
    {
        $request->validate(['rating' => 'required|integer|between:1,5']);
        $ride = Ride::findOrFail($rideId);
        Rating::updateOrCreate(
            ['rater_id' => Auth::id(), 'ride_id' => $rideId],
            ['ratee_id' => $request->ratee_id, 'rating' => $request->rating, 'comment' => $request->comment]
        );
        return back()->with('flash_success', 'Rating submitted!');
    }
}
