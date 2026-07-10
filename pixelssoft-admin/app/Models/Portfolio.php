<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'slug', 'title', 'category', 'description', 'image',
        'client', 'project_date', 'tags', 'sort_order', 'status',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->orderBy('sort_order');
    }
}
