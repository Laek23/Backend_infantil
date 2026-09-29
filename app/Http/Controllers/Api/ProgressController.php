<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $progress = LearningProgress::query()
            ->where('user_id', $request->user()->id)
            ->pluck('completed', 'subject');

        return response()->json([
            'user_id' => $request->user()->id,
            'progress' => $progress->map(fn ($completed) => (int) $completed),
        ]);
    }

    public function update(Request $request, string $subject): JsonResponse
    {
        $validated = $request->validate([
            'completed' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $progress = LearningProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'subject' => $subject,
            ],
            ['player_id' => (string) $request->user()->id, 'completed' => $validated['completed']],
        );

        return response()->json([
            'subject' => $progress->subject,
            'completed' => $progress->completed,
        ]);
    }
}