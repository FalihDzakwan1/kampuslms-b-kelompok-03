<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Grade;
use App\Http\Resources\GradeResource;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * PUT /api/v1/submissions/{submission}/grade
     * Dosen pemilik — upsert (updateOrCreate)
     * Menggunakan updateOrCreate karena grades.submission_id bersifat unique.
     * Kembalikan 201 saat nilai dibuat pertama kali, dan 200 saat diperbarui.
     * Eager loading: assignment.course
     */
    public function upsert(Request $request, Submission $submission)
    {
        $submission->load('assignment.course');
        $user = $request->user();

        abort_unless(
            $user->role === 'dosen'
            && $submission->assignment->course->lecturer_id === $user->id,
            403, 'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $validated = $request->validate([
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:2000',
        ]);

        $exists = $submission->grade()->exists();

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $user->id,
                'score' => $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        $statusCode = $exists ? 200 : 201;

        return (new GradeResource($grade))
            ->response()
            ->setStatusCode($statusCode);
    }
}
