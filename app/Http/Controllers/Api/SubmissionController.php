<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Http\Resources\SubmissionResource;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    /**
     * POST /api/v1/assignments/{assignment}/submissions
     * Mahasiswa terdaftar — multipart file upload
     * Eager loading: course.students (untuk cek enrollment)
     */
    public function store(Request $request, Assignment $assignment)
    {
        $user = $request->user();
        abort_unless($user->role === 'mahasiswa', 403, 'Anda tidak memiliki akses ke sumber daya ini.');

        // Cek apakah mahasiswa terdaftar di mata kuliah ini
        $assignment->load('course');
        $isEnrolled = $assignment->course
            ->students()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isEnrolled, 403, 'Anda tidak terdaftar di mata kuliah ini.');

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'note' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $path = $file->store('submissions', 'public');

        // Cek apakah terlambat
        $isLate = $assignment->due_at && now()->gt($assignment->due_at);

        if ($isLate && !$assignment->allow_late) {
            return response()->json([
                'message' => 'Batas waktu pengumpulan sudah lewat dan tugas ini tidak mengizinkan keterlambatan.',
            ], 422);
        }

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'note' => $validated['note'] ?? null,
            'submitted_at' => now(),
            'is_late' => $isLate,
        ]);

        return (new SubmissionResource($submission))
            ->response()
            ->setStatusCode(201);
    }
}
