<?php

namespace App\Models\Freelancer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FreelancerSkill extends Model
{
    use HasFactory;
    protected $table = 'freelancer_skills';

    protected $fillable = [
        'freelancer_account_id', 'freelancer_skill_id', 'name', 'seo_url',
        'is_primary', 'is_secondary', 'automation_enabled', 'priority', 'raw',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_secondary' => 'boolean',
            'automation_enabled' => 'boolean',
            'raw' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FreelancerAccount::class, 'freelancer_account_id');
    }

    public function portfolioLinks(): BelongsToMany
    {
        return $this->belongsToMany(FreelancerPortfolioLink::class, 'freelancer_portfolio_link_skill', 'skill_id', 'portfolio_link_id')->withTimestamps();
    }
}


