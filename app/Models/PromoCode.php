<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = ['code','discount_type','discount_value','max_uses','uses_count',
        'min_fare','max_discount','starts_at','expires_at','is_active'];
    protected $casts = ['is_active'=>'boolean','starts_at'=>'datetime','expires_at'=>'datetime'];

    public function isValid($fare = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->uses_count >= $this->max_uses) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->min_fare && $fare < $this->min_fare) return false;
        return true;
    }

    public function calculate($fare): float
    {
        $disc = $this->discount_type === 'percentage'
            ? $fare * ($this->discount_value / 100)
            : $this->discount_value;
        if ($this->max_discount) $disc = min($disc, $this->max_discount);
        return $disc;
    }
}
