<?php

namespace App\Http\Controllers;

use App\Models\Course;

class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah
    public function index()
    {
        $courses = Course::all();

        return view('courses.index', compact('courses'));
    }


    // Menampilkan detail satu mata kuliah
    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }


    // Menampilkan form tambah mata kuliah
    public function create()
    {
        return view('courses.create');
    }

      public function store()
    {
        Course::create([
            'name' => request('name')
        ]);

        return redirect('/courses');
    }

}