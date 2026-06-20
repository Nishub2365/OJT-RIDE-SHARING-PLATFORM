<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    protected $fillable = ['user_id','doc_type','doc_path','status','rejection_reason'];
    public function user() { return $this->belongsTo(User::class); }
}
