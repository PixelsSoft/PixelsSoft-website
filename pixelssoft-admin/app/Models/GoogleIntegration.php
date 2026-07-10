<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleIntegration extends Model
{
    protected $fillable = ['service_key', 'enabled', 'config'];

    protected $casts = [
        'enabled' => 'boolean',
        'config' => 'array',
    ];
}
