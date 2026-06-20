<?php
namespace App\Http\Controllers;
use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::where('user_id', Auth::id())
            ->orderByDesc('created_at')->take(30)->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'body'       => $n->body,
                'is_read'    => (bool)$n->is_read,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        AppNotification::where('user_id', Auth::id())->where('is_read', false)->update(['is_read' => true]);
        return response()->json($notifications);
    }

    public function count()
    {
        $count = AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }
}
