<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller
{
    public function index(): JsonResponse
    {
        $ranking = User::query()
            ->leftJoin('learning_progress', 'users.id', '=', 'learning_progress.user_id')
            ->select('users.id', 'users.name')
            ->where('users.role', 'child')
            ->selectRaw('COALESCE(SUM(learning_progress.completed), 0) as completed')
            ->addSelect('users.total_xp')
            ->groupBy('users.id', 'users.name', 'users.total_xp')
            ->orderByDesc('users.total_xp')
            ->orderByDesc('completed')
            ->orderBy('users.name')
            ->limit(20)
            ->get();

        return response()->json(['ranking' => $ranking]);
    }
}