<?php

namespace App\Http\Controllers;

use App\Models\Course;

class CourseController extends Controller
{
    public function show()
    {
        $courses = Course::all();
        return view('/courses/index', ['courses' => $courses]);
    }
}