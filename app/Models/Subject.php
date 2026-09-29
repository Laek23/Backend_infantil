<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'icon', 'accent', 'active'])]
class Subject extends Model
{
    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}