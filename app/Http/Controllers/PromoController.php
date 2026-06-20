<?php
namespace App\Http\Controllers;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $promo = PromoCode::where('code', strtoupper($request->code))
            ->where('is_active', true)->first();

        if (!$promo) return response()->json(['valid' => false, 'message' => 'Invalid promo code.']);
        if ($promo->expires_at && $promo->expires_at->isPast()) return response()->json(['valid' => false, 'message' => 'Promo code expired.']);
        if ($promo->uses_count >= $promo->max_uses) return response()->json(['valid' => false, 'message' => 'Promo code fully used.']);

        $msg = $promo->discount_type === 'percentage'
            ? "✅ {$promo->discount_value}% discount applied!"
            : "✅ NPR {$promo->discount_value} discount applied!";

        return response()->json(['valid' => true, 'message' => $msg]);
    }
}
