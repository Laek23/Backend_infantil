<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LivesController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            return response()->json(['lives' => null, 'maximum' => null, 'premium' => false, 'reset_at' => null, 'unlimited' => true]);
        }
        $this->refresh($user);

        return response()->json($this->payload($user));
    }

    public function fail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return response()->json(['lives' => null, 'unlimited' => true]);
        }

        $this->refresh($user);
        if ($user->lives > 0) {
            $user->decrement('lives');
            $user->refresh();
        }

        return response()->json($this->payload($user));
    }

    private function refresh($user): void
    {
        if ($user->role === 'admin') {
            return;
        }

        $now = Carbon::now();
        $premium = $user->premium_until && $user->premium_until->isFuture();
        $maximum = $premium ? 14 : 7;

        if (!$user->lives_reset_at || $user->lives_reset_at->addHours(5)->lte($now)) {
            $user->forceFill(['lives' => $maximum, 'lives_reset_at' => $now])->save();
        } elseif (!$premium && $user->lives > 7) {
            $user->forceFill(['lives' => 7])->save();
        }
    }

    private function payload($user): array
    {
        return [
            'lives' => (int) $user->lives,
            'maximum' => $user->premium_until?->isFuture() ? 14 : 7,
            'premium' => (bool) ($user->premium_until?->isFuture()),
            'reset_at' => $user->lives_reset_at?->copy()->addHours(5)->toIso8601String(),
            'unlimited' => false,
        ];
    }
}