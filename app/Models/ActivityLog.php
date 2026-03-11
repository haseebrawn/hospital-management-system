<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
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