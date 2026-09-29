<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Assignment;
use Illuminate\Http\Request;


class SubmissionController extends Controller
{

    public function index()
    {

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


        return back();

    }





    public function show(
        Submission $submission
    )
    {


        abort_unless(

            $submission->student_id === auth()->id()
            ||
            auth()->user()->role === 'admin'
            ||
            $submission->assignment
                ->course
                ->lecturer_id === auth()->id(),

            403

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

        abort_unless(
            $submission->student_id === auth()->id(),
            403
        );


        $submission->delete();


        return back();

    }

}