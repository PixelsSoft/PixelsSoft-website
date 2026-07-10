<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $table = 'crm_tags';

    protected $fillable = ['name', 'color'];

    public function leads(): MorphToMany
    {
        return $this->morphedByMany(Lead::class, 'taggable', 'crm_taggables');
    }

    public function deals(): MorphToMany
    {
        return $this->morphedByMany(Deal::class, 'taggable', 'crm_taggables');
    }
}
