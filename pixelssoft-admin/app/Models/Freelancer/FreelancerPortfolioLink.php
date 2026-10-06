<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerPortfolioLink extends Model
{
    use HasFactory;
    protected $table = 'freelancer_portfolio_links';

    protected $fillable = [
        'freelancer_account_id', 'title', 'url', 'description', 'image', 'technologies',
        'priority', 'is_active', 'usage_count', 'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(FreelancerSkill::class, 'freelancer_portfolio_link_skill', 'portfolio_link_id', 'skill_id')->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(FreelancerCategory::class, 'freelancer_portfolio_link_category', 'portfolio_link_id', 'category_id')->withTimestamps();
    }

    public function bids(): HasMany
    {
        return $this->hasMany(FreelancerBid::class, 'freelancer_project_id', 'id');
    }
}


