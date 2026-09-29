<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'email', 'password', 'role', 'lives', 'lives_reset_at', 'premium_until', 'stripe_checkout_session_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lives' => 'integer',
            'total_xp' => 'integer',
            'equipped_reward_id' => 'integer',
            'equipped_customizations' => 'array',
            'lives_reset_at' => 'datetime',
            'premium_until' => 'datetime',
            'stripe_checkout_session_id' => 'string',
        ];
    }

    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class)->withPivot('unlocked_at');
    }
}
