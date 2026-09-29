<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $unlockedIds = $user->rewards()->pluck('rewards.id');
        return response()->json([
            'total_xp' => (int) $user->total_xp,
            'equipped_reward_id' => $user->equipped_reward_id,
            'equipped_customizations' => $user->equipped_customizations ?? [],
            'rewards' => Reward::where('active', true)->orderBy('unlock_xp')->get()->map(fn ($reward) => [
                ...$reward->toArray(),
                'unlocked' => $unlockedIds->contains($reward->id),
                'equipped' => $user->equipped_reward_id === $reward->id,
            ]),
        ]);
    }

    public function equip(Request $request, Reward $reward): JsonResponse
    {
        abort_unless($request->user()->rewards()->whereKey($reward->id)->exists(), 403, 'Aún no desbloqueas esta recompensa.');
        $customizations = $request->user()->equipped_customizations ?? [];
        if (($customizations[$reward->category] ?? null) === $reward->id) {
            unset($customizations[$reward->category]);
        } else {
            $customizations[$reward->category] = $reward->id;
        }
        $request->user()->forceFill([
            'equipped_customizations' => $customizations,
            'equipped_reward_id' => $reward->id,
        ])->save();
        return response()->json(['equipped_customizations' => $customizations]);
    }
}