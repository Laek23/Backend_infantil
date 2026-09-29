<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['subject_id', 'type', 'title', 'prompt', 'hint', 'answer', 'content', 'active', 'position'])]
class Activity extends Model
{
    protected function casts(): array
    {
        return ['content' => 'array', 'active' => 'boolean'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}