<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\LearningProgress;
use App\Models\Reward;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Subject $subject): JsonResponse
    {
        return response()->json(['activities' => $subject->activities()->where('active', true)->orderBy('position')->get([
            'id', 'subject_id', 'type', 'title', 'prompt', 'hint', 'content', 'active', 'position',
        ])]);
    }

    public function adminIndex(Request $request, Subject $subject): JsonResponse
    {
        $this->ensureAdmin($request);
        return response()->json(['activities' => $subject->activities()->orderBy('position')->get()]);
    }

    public function store(Request $request, Subject $subject): JsonResponse
    {
        $this->ensureAdmin($request);
        $data = $this->validated($request);
        $data['subject_id'] = $subject->id;
        $data['position'] = $subject->activities()->max('position') + 1;
        $data['active'] = $data['active'] ?? true;
        return response()->json(['activity' => Activity::create($data)], 201);
    }

    public function update(Request $request, Activity $activity): JsonResponse
    {
        $this->ensureAdmin($request);
        $activity->update($this->validated($request, true));
        return response()->json(['activity' => $activity->fresh()]);
    }

    public function destroy(Request $request, Activity $activity): JsonResponse
    {
        $this->ensureAdmin($request);
        $activity->delete();
        return response()->json(['message' => 'Actividad eliminada.']);
    }

    public function answer(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate(['answer' => ['required', 'string', 'max:255']]);
        $user = $request->user();
        abort_unless($activity->active, 404);

        return DB::transaction(function () use ($activity, $user, $data) {
            $user = $user->newQuery()->lockForUpdate()->findOrFail($user->id);
            if ($user->role !== 'admin') {
                $this->refreshLives($user);
                if ($user->lives < 1) {
                    return response()->json(['message' => 'No te quedan vidas.', 'lives' => 0], 423);
                }
            }
            if ($activity->type === 'memorama') {
                $submittedPairs = json_decode($data['answer'], true);
                $expectedPairs = array_values(array_unique($activity->content['cards'] ?? []));
                $submittedPairs = is_array($submittedPairs) ? array_values(array_unique($submittedPairs)) : [];
                sort($expectedPairs);
                sort($submittedPairs);
                $isCorrect = $submittedPairs === $expectedPairs;
            } else {
                $isCorrect = mb_strtoupper(trim($data['answer'])) === mb_strtoupper(trim($activity->answer));
            }

            if (!$isCorrect) {
                if ($user->role !== 'admin') {
                    if ($user->lives > 0) $user->decrement('lives');
                }
                return response()->json(['correct' => false, 'lives' => $user->role === 'admin' ? null : $user->fresh()->lives]);
            }

            $progress = LearningProgress::firstOrNew([
                'user_id' => $user->id,
                'subject' => $activity->subject->slug,
            ]);
            $progress->player_id = (string) $user->id;
            $progress->completed = ((int) $progress->completed) + 1;
            $progress->save();

            $user->increment('total_xp', 10);
            $user->refresh();
            $unlocked = Reward::where('active', true)->where('unlock_xp', '<=', $user->total_xp)->get();
            $ownedIds = $user->rewards()->pluck('rewards.id');
            $newRewards = $unlocked->whereNotIn('id', $ownedIds);
            $user->rewards()->syncWithoutDetaching($unlocked->pluck('id'));

            return response()->json([
                'correct' => true,
                'completed' => $progress->completed,
                'total_xp' => $user->total_xp,
                'new_rewards' => $newRewards->values(),
            ]);
        });
    }

    private function validated(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'type' => [$partial ? 'sometimes' : 'required', 'in:sumador,sopa,crucigrama,memorama,tiempo,adivina'],
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:120'],
            'prompt' => [$partial ? 'sometimes' : 'required', 'string', 'max:1000'],
            'hint' => ['nullable', 'string', 'max:255'],
            'answer' => [$partial || $request->input('type') === 'memorama' ? 'sometimes' : 'required', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'active' => ['sometimes', 'boolean'],
        ]);
    }

    private function refreshLives($user): void
    {
        $premium = $user->premium_until?->isFuture() ?? false;
        $maximum = $premium ? 14 : 7;
        if (!$user->lives_reset_at || $user->lives_reset_at->copy()->addHours(5)->isPast()) {
            $user->forceFill(['lives' => $maximum, 'lives_reset_at' => now()])->save();
        }
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403, 'Se requiere una cuenta administradora.');
    }
}