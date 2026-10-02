<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $assignments = Assignment::query()
            ->with(['course', 'creator'])
            ->withCount('submissions')
            ->when($request->filled('course_id'), function ($query) use ($request) {
                $query->where('course_id', $request->course_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return AssignmentResource::collection($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Mahasiswa tidak berhak membuat tugas (harus 403, bukan 401)
        if (! in_array($user?->role, ['admin', 'dosen'], strict: true)) {
            abort(403, 'Hanya dosen atau admin yang dapat membuat tugas.');
        }

        $validated = $request->validate([
            'course_id'    => ['required', 'exists:courses,id'],
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at'       => ['required', 'date'],
            'max_score'    => ['nullable', 'integer', 'between:1,100'],
            'allow_late'   => ['nullable', 'boolean'],
            'status'       => ['required', 'in:draft,published'],
        ]);

        $course = Course::findOrFail($validated['course_id']);

        // Jika dosen, pastikan mengampu mata kuliah ini
        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            abort(403, 'Anda bukan dosen pengampu mata kuliah ini.');
        }

        $validated['created_by'] = $user->id;
        $validated['max_score']  = $validated['max_score'] ?? 100;
        $validated['allow_late'] = $validated['allow_late'] ?? false;

        $assignment = Assignment::create($validated);
        $assignment->load(['course', 'creator'])->loadCount('submissions');

        return (new AssignmentResource($assignment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Assignment $assignment): AssignmentResource
    {
        $assignment->load(['course', 'creator'])->loadCount('submissions');

        return new AssignmentResource($assignment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assignment $assignment): AssignmentResource
    {
        $user = $request->user();

        if ($user?->role === 'mahasiswa') {
            abort(403, 'Mahasiswa tidak berhak mengubah tugas.');
        }

        if ($user?->role === 'dosen' && $assignment->created_by !== $user->id && $assignment->course?->lecturer_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah tugas ini.');
        }

        $validated = $request->validate([
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at'       => ['sometimes', 'required', 'date'],
            'max_score'    => ['nullable', 'integer', 'between:1,100'],
            'allow_late'   => ['nullable', 'boolean'],
            'status'       => ['sometimes', 'required', 'in:draft,published'],
        ]);

        $assignment->update($validated);
        $assignment->load(['course', 'creator'])->loadCount('submissions');

        return new AssignmentResource($assignment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Assignment $assignment): Response
    {
        $user = $request->user();

        if ($user?->role === 'mahasiswa') {
            abort(403, 'Mahasiswa tidak berhak menghapus tugas.');
        }

        if ($user?->role === 'dosen' && $assignment->created_by !== $user->id && $assignment->course?->lecturer_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus tugas ini.');
        }

        $assignment->delete();

        return response()->noContent();
    }
}
