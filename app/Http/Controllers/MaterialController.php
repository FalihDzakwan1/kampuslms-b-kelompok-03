<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Course;
use Illuminate\Http\Request;


class MaterialController extends Controller
{


    public function index(Course $course)
    {

        abort_unless(
            $course->lecturer_id === auth()->id(),
            403
        );


        return view(
            'materials.index',
            [
                'course' => $course,
                'materials' => $course->materials
            ]
        );

    }





    public function create(Course $course)
    {

        abort_unless(
            $course->lecturer_id === auth()->id(),
            403
        );


        return view(
            'materials.create',
            compact('course')
        );

    }





    public function store(
        Request $request,
        Course $course
    )
    {


        abort_unless(
            $course->lecturer_id === auth()->id(),
            403
        );



        $validated = $request->validate([

            'title' => 'required|string',
            'description' => 'nullable|string',
            'file' => 'nullable|file'

        ]);



        if($request->hasFile('file')){

            $validated['file'] =
                $request
                ->file('file')
                ->store('materials');

        }



        $course->materials()->create($validated);



        return redirect()
            ->route(
                'dosen.courses.materials.index',
                $course
            );

    }





    public function show(
        Course $course,
        Material $material
    )
    {


        /*
        Cek material benar milik course tersebut
        mencegah nested route IDOR
        */

        abort_unless(
            $material->course_id === $course->id,
            403
        );



        return view(
            'materials.show',
            compact(
                'course',
                'material'
            )
        );

    }





    public function edit(
        Course $course,
        Material $material
    )
    {


        abort_unless(

            $course->lecturer_id === auth()->id()
            &&
            $material->course_id === $course->id,

            403

        );



        return view(
            'materials.edit',
            compact(
                'course',
                'material'
            )
        );

    }





    public function update(
        Request $request,
        Course $course,
        Material $material
    )
    {


        abort_unless(

            $course->lecturer_id === auth()->id()
            &&
            $material->course_id === $course->id,

            403

        );



        $validated = $request->validate([

            'title'=>'required|string',
            'description'=>'nullable|string'

        ]);



        $material->update($validated);



        return redirect()
            ->back();

    }





    public function destroy(
        Course $course,
        Material $material
    )
    {


        abort_unless(

            $course->lecturer_id === auth()->id()
            &&
            $material->course_id === $course->id,

            403

        );



        $material->delete();



        return redirect()
            ->back();

    }


}