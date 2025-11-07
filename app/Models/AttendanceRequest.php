<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    protected $fillable = [
        'user_id', 'date', 'check_in', 'check_out', 'reason', 'status', 'approved_by', 'approved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
