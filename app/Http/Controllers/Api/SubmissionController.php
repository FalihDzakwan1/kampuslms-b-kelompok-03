<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Http\Resources\SubmissionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * POST /api/v1/assignments/{assignment}/submissions
     * Mahasiswa terdaftar — multipart file upload (disimpan di disk privat 'local')
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
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:2048'], // Max 2MB
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Cek apakah terlambat sebelum menyimpan berkas ke disk
        $isLate = $assignment->due_at && now()->gt($assignment->due_at);

        if ($isLate && !$assignment->allow_late) {
            return response()->json([
                'message' => 'Batas waktu pengumpulan sudah lewat dan tugas ini tidak mengizinkan keterlambatan.',
            ], 422);
        }

        $file = $request->file('file');
        // Simpan di disk privat ('local' -> storage/app/private), BUKAN 'public'
        $path = $file->store("submissions/{$assignment->id}", 'local');

        // Hapus berkas fisik lama jika mahasiswa mengumpulkan ulang
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->file_path && Storage::disk('local')->exists($existing->file_path)) {
            Storage::disk('local')->delete($existing->file_path);
        }

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id'       => $user->id,
            ],
            [
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size'     => $file->getSize(),
                'note'          => $validated['note'] ?? null,
                'submitted_at'  => now(),
                'is_late'       => $isLate,
            ]
        );

        return (new SubmissionResource($submission))
            ->response()
            ->setStatusCode(201);
    }
}
