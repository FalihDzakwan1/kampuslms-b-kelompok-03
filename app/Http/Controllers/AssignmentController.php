<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;


class AssignmentController extends Controller
{


    public function index(Course $course)
    {
        return view(
            'assignments.index',
            [
                'course' => $course,
                'assignments' => $course->assignments
            ]
        );
    }



    public function create(Course $course)
    {
        return view(
            'assignments.create',
            compact('course')
        );
    }



    public function store(
        Request $request,
        Course $course
    ) {

        abort_unless(
            $course->lecturer_id === auth()->id(),
            403
        );


        $course->assignments()->create(

            $request->validate([
                'title'=>'required|string',
                'description'=>'nullable|string',
                'deadline'=>'nullable|date'
            ])

        );


        return back();

    }




    public function show(
        Course $course,
        Assignment $assignment
    ) {


        abort_unless(
            $assignment->course_id === $course->id,
            403
        );


        return view(
            'assignments.show',
            compact(
                'course',
                'assignment'
            )
        );

    }





    public function update(
        Request $request,
        Course $course,
        Assignment $assignment
    ) {


        abort_unless(
            $course->lecturer_id === auth()->id()
            &&
            $assignment->course_id === $course->id,
            403
        );


        $assignment->update(
            $request->validate([
                'title'=>'required|string'
            ])
        );


        return back();

    }





    public function destroy(
        Course $course,
        Assignment $assignment
    ) {


        abort_unless(
            $course->lecturer_id === auth()->id()
            &&
            $assignment->course_id === $course->id,
            403
        );


        $assignment->delete();


        return back();

    }

}