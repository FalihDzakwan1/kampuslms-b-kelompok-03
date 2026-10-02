<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Grade;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index()
    {
        // Mahasiswa hanya melihat submission miliknya sendiri
        $submissions = Submission::with(['assignment.course'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view(
            'submissions.index',
            compact('submissions')
        );
    }

    public function store(
        Request $request,
        Assignment $assignment
    )
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'note' => 'nullable|string|max:500'
        ]);

        $isLate = $assignment->due_at ? now()->gt($assignment->due_at) : false;

        if ($isLate && !$assignment->allow_late) {
            return back()->with('error', 'Batas waktu pengumpulan telah lewat dan tugas ini tidak mengizinkan keterlambatan.');
        }

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('submissions', 'public');

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

        return back()->with('success', 'Tugas berhasil dikumpulkan!');
    }

    public function show(
        Submission $submission
    )
    {
        // TODO: Akan direfaktor menjadi SubmissionPolicy@view di minggu 7
        abort_unless(
            $submission->user_id === auth()->id()
            || auth()->user()->role === 'admin'
            || $submission->assignment->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk melihat pengumpulan tugas ini.'
        );

        $submission->load(['assignment.course', 'student']);

        return view(
            'submissions.show',
            compact('submission')
        );
    }

    public function destroy(
        Submission $submission
    )
    {
        // TODO: Akan direfaktor menjadi SubmissionPolicy@delete di minggu 7
        abort_unless(
            $submission->user_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk membatalkan pengumpulan tugas ini.'
        );

        $submission->delete();

        return back()->with('success', 'Pengumpulan tugas berhasil dibatalkan.');
    }

    public function grade(
        Request $request,
        Submission $submission
    ) {
        $submission->load('assignment.course');

        abort_unless(
            auth()->user()->role === 'dosen'
            && $submission->assignment->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk menilai pengumpulan tugas ini.'
        );

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