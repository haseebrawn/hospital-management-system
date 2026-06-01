<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'email',
        'user_id',
        'ip_address',
        'success',
        'notes',
        'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
