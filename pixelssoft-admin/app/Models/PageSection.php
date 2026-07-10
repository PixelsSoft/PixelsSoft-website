<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = ['page_key', 'section_key', 'content', 'sort_order'];

    protected $casts = [
        'content' => 'array',
    ];
}
