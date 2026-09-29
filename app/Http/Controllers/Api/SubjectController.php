<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['subjects' => Subject::withCount(['activities' => fn ($query) => $query->where('active', true)])
            ->where('active', true)
            ->whereHas('activities', fn ($query) => $query->where('active', true))
            ->orderBy('name')
            ->get()]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        return response()->json(['subjects' => Subject::withCount('activities')->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:subjects,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'accent' => ['nullable', 'string', 'max:20'],
            'active' => ['sometimes', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return response()->json(['subject' => Subject::create($data)], 201);
    }

    public function update(Request $request, Subject $subject): JsonResponse
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['sometimes', 'string', 'max:50'],
            'accent' => ['sometimes', 'string', 'max:20'],
            'active' => ['sometimes', 'boolean'],
        ]);
        $subject->update($data);

        return response()->json(['subject' => $subject->fresh()]);
    }

    public function destroy(Request $request, Subject $subject): JsonResponse
    {
        $this->ensureAdmin($request);
        $subject->delete();

        return response()->json(['message' => 'Materia eliminada.']);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403, 'Se requiere una cuenta administradora.');
    }
}