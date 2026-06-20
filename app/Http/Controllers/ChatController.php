<?php
namespace App\Http\Controllers;
use App\Models\ChatMessage;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /** GET /chat/{ride}/{lastId} — poll new messages */
    public function poll($rideId, $lastId = 0)
    {
        $ride = Ride::findOrFail($rideId);
        $this->authorize_ride($ride);

        $messages = ChatMessage::where('ride_id', $rideId)
            ->where('id', '>', $lastId)
            ->orderBy('id')->get()
            ->map(fn($m) => [
                'id'      => $m->id,
                'message' => htmlspecialchars($m->message),
                'mine'    => $m->sender_id === Auth::id(),
                'time'    => $m->created_at->format('H:i'),
            ]);

        return response()->json($messages);
    }

    /** POST /chat/{ride} — send message */
    public function send(Request $request, $rideId)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $ride = Ride::findOrFail($rideId);
        $this->authorize_ride($ride);

        ChatMessage::create([
            'ride_id'   => $rideId,
            'sender_id' => Auth::id(),
            'message'   => $request->message,
        ]);

        return response()->json(['ok' => true]);
    }

    private function authorize_ride(Ride $ride)
    {
        $uid = Auth::id();
        if ($ride->rider_id !== $uid && $ride->driver_id !== $uid && Auth::user()->role !== 'admin') {
            abort(403);
        }
    }
}
