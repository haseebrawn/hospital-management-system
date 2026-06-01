<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'user_id',
        'ip_address',
        'method',
        'path',
        'request_payload',
        'response_summary',
        'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
