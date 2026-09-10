<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah
    public function index()
    {
        $courses = Course::with('lecturer')->get();
        return view('courses.index', compact('courses'));
    }


    // Menampilkan detail satu mata kuliah
    public function show(Course $course)
    {
        $course->load('lecturer');

        return view('courses.show', compact('course'));
    }
    
    // Menampilkan form tambah mata kuliah
    public function create()
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', compact('lecturers'));
    }

      public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sks' => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,active,archived',
        ]);

        Course::create($validated);

        return redirect()->route('courses.index');
    }
    // Menampilkan form edit
    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact(
            'course',
            'lecturers'
        ));
    }


    // Update data mata kuliah
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses,code,' . $course->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sks' => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,active,archived',
        ]);

        $course->update($validated);

        return redirect()
            ->route('courses.show', $course);
    }

    //Hapus data mata kuliah
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
         ->route('courses.index');
    }   
}