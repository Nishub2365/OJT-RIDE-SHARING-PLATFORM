<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id','vehicle_type','vehicle_model','vehicle_number','vehicle_color',
        'status','rejection_reason','is_online','current_lat','current_lng',
        'total_trips','total_earned',
    ];
    protected $casts = ['is_online' => 'boolean', 'current_lat' => 'decimal:8', 'current_lng' => 'decimal:8'];

    public function user() { return $this->belongsTo(User::class); }
}
