<?php
namespace App\Http\Controllers;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderByDesc('created_at')->paginate(10);
        return view('support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'  => 'required|string|max:150',
            'message'  => 'required|string|max:2000',
            'priority' => 'required|in:normal,high,urgent',
        ]);
        SupportTicket::create([
            'user_id'  => Auth::id(),
            'subject'  => $request->subject,
            'message'  => $request->message,
            'priority' => $request->priority,
            'status'   => 'open',
        ]);
        return back()->with('flash_success', 'Ticket submitted! Our team will respond within 24 hours.');
    }
}
