<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $fillable = ['sender_id','driver_id','pickup_address','delivery_address',
        'item_type','item_description','weight_kg','recipient_name','recipient_phone',
        'fare','payment_method','status'];
    protected $casts = ['fare' => 'decimal:2'];
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function driver() { return $this->belongsTo(User::class, 'driver_id'); }
}
