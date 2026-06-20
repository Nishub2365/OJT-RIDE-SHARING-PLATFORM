<?php
namespace App\Http\Controllers;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')->paginate(20);
        return view('wallet.index', compact('user', 'transactions'));
    }

    public function topup(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:100|max:50000']);
        $user = Auth::user();
        $user->increment('wallet_balance', $request->amount);
        WalletTransaction::create([
            'user_id'     => $user->id,
            'type'        => 'credit',
            'amount'      => $request->amount,
            'description' => 'Wallet top-up',
        ]);
        return back()->with('flash_success', 'NPR ' . number_format($request->amount) . ' added to wallet!');
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:100',
            'account_name'   => 'required|string',
            'bank_name'      => 'required|string',
            'account_number' => 'required|string',
        ]);
        $user = Auth::user();
        if ($user->wallet_balance < $request->amount) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance.']);
        }
        $user->decrement('wallet_balance', $request->amount);
        WalletTransaction::create([
            'user_id'     => $user->id,
            'type'        => 'debit',
            'amount'      => $request->amount,
            'description' => "Withdrawal to {$request->bank_name} ({$request->account_number})",
        ]);
        return back()->with('flash_success', 'Withdrawal request submitted! Processing in 1–2 business days.');
    }
}
