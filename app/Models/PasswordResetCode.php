<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'password_reset_codes';

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}