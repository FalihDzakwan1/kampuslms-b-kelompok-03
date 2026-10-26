<?php

namespace App\Http\Controllers;

use App\Events\GradeGiven;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // 1. is_late dihitung di server menggunakan now()
        $isLate = $assignment->due_at ? now()->gt($assignment->due_at) : false;

        // 2. Tolak jika allow_late bernilai false dan sudah lewat batas waktu
        if ($isLate && !$assignment->allow_late) {
            return back()->with('error', 'Batas waktu pengumpulan telah lewat dan tugas ini tidak mengizinkan keterlambatan.');
        }

        $uploadedFile = $request->file('file');

        // 3. Simpan ke folder privat 'local' dengan nama acak
        $path = $uploadedFile->store("submissions/{$assignment->id}", 'local');

        // 4. Hapus berkas fisik lama dari harddisk jika mahasiswa mengumpulkan ulang
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing && $existing->file_path && Storage::disk('local')->exists($existing->file_path)) {
            Storage::disk('local')->delete($existing->file_path);
        }

        // 5. updateOrCreate menegakkan unique composite (mencegah data dobel)
        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id'       => auth()->id(),
            ],
            [
                'file_path'     => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_size'     => $uploadedFile->getSize(),
                'note'          => $request->note,
                'submitted_at'  => now(),
                'is_late'       => $isLate,
            ]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
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

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => auth()->id(),
                'score'     => $request->score,
                'feedback'  => $request->feedback,
                'graded_at' => now(),
            ]
        );

        // Beri tahu mahasiswa pemilik submission (diproses di latar lewat queue).
        GradeGiven::dispatch($grade, isUpdate: ! $grade->wasRecentlyCreated);

        return back()->with('success', 'Nilai dan umpan balik berhasil disimpan.');
    }

    /**
     * Unduh berkas jawaban mahasiswa.
     * Memakai SubmissionPolicy@view: pemilik, dosen MK terkait, atau admin.
     */
    public function download(Submission $submission)
    {
        $submission->loadMissing('assignment.course');

        Gate::authorize('view', $submission);

        abort_unless(
            $submission->file_path && Storage::disk('local')->exists($submission->file_path),
            404,
            'Berkas tidak ditemukan.'
        );

        return Storage::disk('local')->download($submission->file_path, $submission->original_name);
    }
}