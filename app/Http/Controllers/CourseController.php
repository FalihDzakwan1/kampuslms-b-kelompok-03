<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

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

      public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'  => 'required|string|max:20|unique:courses,kode',
            'nama'  => 'required|string|max:255',
            'sks'   => 'required|integer|min:1|max:6',
            'dosen' => 'required|string|max:255',
        ]);

        Course::create($validated);

        return redirect()->route('courses.index');
    }

}