<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;


class CourseController extends Controller
{

    public function index(Request $request)
    {

        $courses = Course::query()
            ->with('lecturer')
            ->withCount([
                'materials',
                'assignments'
            ])
            ->when(
                $request->filled('q'),
                function ($query) use ($request) {

                    $query->where(function ($q) use ($request) {

                        $q->where(
                            'name',
                            'like',
                            '%' . $request->q . '%'
                        )
                        ->orWhere(
                            'code',
                            'like',
                            '%' . $request->q . '%'
                        );

                    });

                }
            )
            ->paginate(15);


        return CourseResource::collection(
            $courses
        );

    }



    public function show(Course $course)
    {

        $course->load('lecturer');


        return new CourseResource(
            $course
        );

    }



    public function store(Request $request)
    {

        $data = $request->validate([

            'code'=>'required',
            'name'=>'required',
            'sks'=>'required|integer',
            'status'=>'required'

        ]);


        $course = Course::create(
            $data
        );


        return new CourseResource(
            $course
        );

    }



    public function update(Request $request, Course $course)
    {

        $course->update(
            $request->validate([

                'code'=>'required',
                'name'=>'required',
                'sks'=>'required|integer',
                'status'=>'required'

            ])
        );


        return new CourseResource(
            $course
        );

    }



    public function destroy(Course $course)
    {

        $course->delete();


        return response()->noContent();

    }

}
