<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['ride_id','sender_id','message'];
    public function ride()   { return $this->belongsTo(Ride::class); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
}
