<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    protected $fillable = [
        'rider_id','driver_id','pickup_address','pickup_lat','pickup_lng',
        'destination_address','destination_lat','destination_lng',
        'vehicle_category','offered_fare','agreed_fare','payment_method',
        'promo_code','discount_amount','status','cancelled_by','scheduled_at','completed_at',
    ];
    protected $casts = [
        'pickup_lat'=>'decimal:8','pickup_lng'=>'decimal:8',
        'destination_lat'=>'decimal:8','destination_lng'=>'decimal:8',
        'scheduled_at'=>'datetime','completed_at'=>'datetime',
    ];

    public function rider()    { return $this->belongsTo(User::class, 'rider_id'); }
    public function driver()   { return $this->belongsTo(User::class, 'driver_id'); }
    public function bids()     { return $this->hasMany(RideBid::class); }
    public function messages() { return $this->hasMany(ChatMessage::class); }
    public function ratings()  { return $this->hasMany(Rating::class); }
    public function sos()      { return $this->hasMany(SosAlert::class); }
}
