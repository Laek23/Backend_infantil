<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'asset', 'description', 'unlock_xp', 'active'])]
class Reward extends Model
{
    protected function casts(): array
    {
        return ['active' => 'boolean', 'unlock_xp' => 'integer'];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('unlocked_at');
    }
}