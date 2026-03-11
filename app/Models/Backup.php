<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
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
