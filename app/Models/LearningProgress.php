<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'player_id', 'subject', 'completed'])]
class LearningProgress extends Model
{
    protected function casts(): array
    {
        return [
            'completed' => 'integer',
        ];
    }
}