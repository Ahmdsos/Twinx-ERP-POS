<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuedLicense extends Model
{
    protected $fillable = [
        'client_name',
        'machine_id',
        'license_key',
        'expires_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
