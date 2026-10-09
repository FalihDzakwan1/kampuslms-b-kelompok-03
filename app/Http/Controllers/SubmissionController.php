<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubmissionController extends Controller
{
    /**
     * Daftar submission milik mahasiswa yang sedang login.
     * Query sudah disaring — mahasiswa HANYA melihat miliknya sendiri.
     */
    public function index()
    {
        $submissions = Submission::with(['assignment.course'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('submissions.index', compact('submissions'));
    }

    /**
     * Kumpulkan tugas baru.
     * SubmissionPolicy@create: Mahasiswa terdaftar di MK + belum pernah submit.
     */
    public function store(Request $request, Assignment $assignment)
    {
        $assignment->loadMissing('course');

        Gate::authorize('create', [Submission::class, $assignment]);

        $request->validate([
            'file' => 'required|file|max:10240',
            'note' => 'nullable|string|max:500',
        ]);

        $isLate = $assignment->due_at ? now()->gt($assignment->due_at) : false;

        if ($isLate && !$assignment->allow_late) {
            return back()->with('error', 'Batas waktu pengumpulan telah lewat dan tugas ini tidak mengizinkan keterlambatan.');
        }

        $uploadedFile = $request->file('file');
        $path         = $uploadedFile->store('submissions');

        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => auth()->id(),
            'file_path'     => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'file_size'     => $uploadedFile->getSize(),
            'note'          => $request->note,
            'submitted_at'  => now(),
            'is_late'       => $isLate,
        ]);

        return back()->with('success', 'Tugas berhasil dikumpulkan!');
    }

    /**
     * Detail satu submission.
     * SubmissionPolicy@view:
     *   Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa (milik sendiri saja) ✅
     * Ini juga melindungi dari IDOR antar mahasiswa.
     */
    public function show(Submission $submission)
    {
        $submission->loadMissing(['assignment.course']);

        Gate::authorize('view', $submission);

        $submission->load(['assignment.course', 'student']);

        return view('submissions.show', compact('submission'));
    }

    /**
     * Hapus submission.
     * SubmissionPolicy@delete: semua peran ditolak (return false).
     * Method ini dipertahankan tetapi akan selalu menghasilkan 403.
     */
    public function destroy(Submission $submission)
    {
        Gate::authorize('delete', $submission);

        $submission->delete();

        return back()->with('success', 'Pengumpulan tugas berhasil dibatalkan.');
    }

    /**
     * Beri / perbarui nilai.
     * GradePolicy@create atau @update (upsert):
     *   Dosen hanya bisa menilai submission dari MK miliknya.
     */
    public function grade(Request $request, Submission $submission)
    {
        $submission->loadMissing(['assignment.course']);

        // Cek apakah sudah ada nilai (update) atau belum (create)
        $existingGrade = $submission->grade;
        if ($existingGrade) {
            Gate::authorize('update', $existingGrade);
        } else {
            Gate::authorize('create', [Grade::class, $submission]);
        }

        $request->validate([
            'score'    => 'required|numeric|min:0|max:' . ($submission->assignment->max_score ?? 100),
            'feedback' => 'nullable|string|max:1000',
        ]);

        Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => auth()->id(),
                'score'     => $request->score,
                'feedback'  => $request->feedback,
                'graded_at' => now(),
            ]
        );

        return back()->with('success', 'Nilai dan umpan balik berhasil disimpan.');
    }
}
