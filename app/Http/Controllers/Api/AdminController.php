<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        return response()->json([
            'players' => User::where('role', 'child')->count(),
            'subscribers' => User::where('role', 'child')->where('premium_until', '>', now())->count(),
            'active_subjects' => \App\Models\Subject::where('active', true)->count(),
            'active_activities' => \App\Models\Activity::where('active', true)->count(),
            'rewards' => Reward::count(),
        ]);
    }

    public function players(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $players = User::where('role', 'child')
            ->orderByDesc('total_xp')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'total_xp', 'lives', 'premium_until', 'created_at']);

        return response()->json(['players' => $players]);
    }

    public function updateSubscription(Request $request, User $user): JsonResponse
    {
        $this->ensureAdmin($request);
        abort_unless($user->role === 'child', 404);

        $data = $request->validate(['active' => ['required', 'boolean']]);
        $user->forceFill([
            'premium_until' => $data['active'] ? Carbon::now()->addDays(30) : null,
            'lives' => $data['active'] ? 14 : min((int) $user->lives, 7),
            'lives_reset_at' => now(),
        ])->save();

        return response()->json(['player' => $user->fresh(['rewards'])]);
    }

    public function rewards(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        return response()->json(['rewards' => Reward::withCount('users')->orderBy('unlock_xp')->get()]);
    }

    public function storeReward(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $reward = Reward::create($this->validateReward($request));

        return response()->json(['reward' => $reward], 201);
    }

    public function updateReward(Request $request, Reward $reward): JsonResponse
    {
        $this->ensureAdmin($request);
        $reward->update($this->validateReward($request, true));

        return response()->json(['reward' => $reward->fresh()]);
    }

    public function deleteReward(Request $request, Reward $reward): JsonResponse
    {
        $this->ensureAdmin($request);
        $reward->delete();

        return response()->json(['message' => 'Personalización eliminada.']);
    }

    private function validateReward(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:100'],
            'category' => [$partial ? 'sometimes' : 'required', 'string', 'max:40'],
            'asset' => [$partial ? 'sometimes' : 'required', 'string', 'max:100'],
            'description' => [$partial ? 'sometimes' : 'required', 'string', 'max:180'],
            'unlock_xp' => [$partial ? 'sometimes' : 'required', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ]);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403, 'Se requiere una cuenta administradora.');
    }
}