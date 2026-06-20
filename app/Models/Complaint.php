<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['complainant_id','accused_id','ride_id','description','status'];
    public function complainant() { return $this->belongsTo(User::class, 'complainant_id'); }
    public function accused()     { return $this->belongsTo(User::class, 'accused_id'); }
    public function ride()        { return $this->belongsTo(Ride::class); }
}
