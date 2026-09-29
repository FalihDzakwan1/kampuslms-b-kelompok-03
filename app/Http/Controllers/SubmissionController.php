<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Assignment;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index()
    {
        // Mahasiswa hanya melihat submission miliknya sendiri
        $submissions = Submission::where(
            'student_id',
            auth()->id()
        )->get();

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
        $validated = $request->validate([
            'file' => 'required|file|max:2048'
        ]);

        $path = $request
            ->file('file')
            ->store('submissions');

        Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => auth()->id(),
            'file' => $path
        ]);

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function show(
        Submission $submission
    )
    {
        // TODO: Akan direfaktor menjadi SubmissionPolicy@view di minggu 7
        abort_unless(
            $submission->student_id === auth()->id()
            || auth()->user()->role === 'admin'
            || $submission->assignment->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk melihat pengumpulan tugas ini.'
        );

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
            $submission->student_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk membatalkan pengumpulan tugas ini.'
        );

        $submission->delete();

        return back()->with('success', 'Pengumpulan tugas berhasil dibatalkan.');
    }
}