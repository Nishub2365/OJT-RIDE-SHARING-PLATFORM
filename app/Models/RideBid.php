<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RideBid extends Model
{
    protected $fillable = ['ride_id','driver_id','bid_amount','message','status'];
    public function ride()   { return $this->belongsTo(Ride::class); }
    public function driver() { return $this->belongsTo(User::class, 'driver_id'); }
}
