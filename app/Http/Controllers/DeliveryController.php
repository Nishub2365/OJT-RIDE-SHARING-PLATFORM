<?php
namespace App\Http\Controllers;
use App\Models\DeliveryOrder;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = DeliveryOrder::where('sender_id', Auth::id())
            ->orderByDesc('created_at')->paginate(15);
        return view('delivery.index', compact('deliveries'));
    }

    public function create()
    {
        return view('delivery.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pickup_address'      => 'required|string',
            'delivery_address'    => 'required|string',
            'item_type'           => 'required|in:document,parcel,freight',
            'item_description'    => 'required|string|max:200',
            'recipient_name'      => 'required|string|max:80',
            'recipient_phone'     => 'required|string|max:15',
            'weight_kg'           => 'nullable|numeric|min:0',
            'payment_method'      => 'required|in:cash,wallet',
        ]);

        $fare = match($request->item_type) {
            'document' => 60,
            'parcel'   => 100 + max(0, (float)$request->weight_kg * 15),
            'freight'  => 500 + max(0, (float)$request->weight_kg * 20),
            default    => 100,
        };

        if ($request->payment_method === 'wallet') {
            $user = Auth::user();
            if ($user->wallet_balance < $fare) {
                return back()->withErrors(['payment_method' => 'Insufficient wallet balance.']);
            }
            $user->decrement('wallet_balance', $fare);
            WalletTransaction::create([
                'user_id'     => $user->id,
                'type'        => 'debit',
                'amount'      => $fare,
                'description' => 'Delivery order fare',
            ]);
        }

        $order = DeliveryOrder::create([
            'sender_id'        => Auth::id(),
            'pickup_address'   => $request->pickup_address,
            'delivery_address' => $request->delivery_address,
            'item_type'        => $request->item_type,
            'item_description' => $request->item_description,
            'weight_kg'        => $request->weight_kg ?? 0,
            'recipient_name'   => $request->recipient_name,
            'recipient_phone'  => $request->recipient_phone,
            'fare'             => $fare,
            'payment_method'   => $request->payment_method,
            'status'           => 'pending',
        ]);

        return redirect()->route('delivery.index')
            ->with('flash_success', "Delivery #{$order->id} placed! Estimated fare: NPR $fare.");
    }
}
