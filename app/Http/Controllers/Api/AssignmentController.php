<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * POST /api/v1/assignments
     * Dosen only — membuat tugas baru
     */
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'dosen', 403, 'Anda tidak memiliki akses ke sumber daya ini.');

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at' => 'nullable|date',
            'max_score' => 'nullable|integer|min:0',
            'allow_late' => 'nullable|boolean',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        abort_unless(
            $course->lecturer_id === $user->id,
            403, 'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $validated['created_by'] = $user->id;
        $assignment = Assignment::create($validated);

        return (new AssignmentResource($assignment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT/PATCH /api/v1/assignments/{assignment}
     * Dosen pemilik
     * Eager loading: course
     */
    public function update(Request $request, Assignment $assignment)
    {
        $assignment->load('course');
        $user = $request->user();

        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403, 'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at' => 'nullable|date',
            'max_score' => 'nullable|integer|min:0',
            'allow_late' => 'nullable|boolean',
            'status' => 'nullable|string|in:draft,published,archived',
        ]);

        $assignment->update($validated);

        return new AssignmentResource($assignment->fresh());
    }

    /**
     * DELETE /api/v1/assignments/{assignment}
     * Dosen pemilik — 204 No Content
     * Eager loading: course
     */
    public function destroy(Request $request, Assignment $assignment)
    {
        $assignment->load('course');
        $user = $request->user();

        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403, 'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $assignment->delete();

        return response()->noContent(); // 204
    }

    /**
     * GET /api/v1/assignments/{assignment}/submissions
     * Dosen pemilik — melihat daftar submission
     * Eager loading: student, grade
     */
    public function submissions(Request $request, Assignment $assignment)
    {
        $assignment->load('course');
        $user = $request->user();

        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403, 'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $submissions = $assignment->submissions()
            ->with(['student', 'grade'])
            ->paginate($request->get('per_page', 15));

        return SubmissionResource::collection($submissions);
    }
}
