<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;

class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah
    public function index(Request $request)
    {
        $courses = Course::query()
            ->with('lecturer')
            ->when($request->filled('q'), fn ($query) =>
                $query->where(fn($q) => 
                    $q->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('code', 'like', '%' . $request->q . '%')
                )
            )
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

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

    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());

        return redirect()->route('courses.index')
                         ->with('success', 'Mata kuliah berhasil ditambahkan.');
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
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    //Hapus data mata kuliah
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
         ->route('courses.index');
    }   
}