<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Backup extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'filename',
        'created_by',
        'storage_path',
        'filesize',
        'notes'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
