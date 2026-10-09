<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;


class CourseController extends Controller
{


    /*
    |--------------------------------------------------------------------------
    | Menampilkan daftar mata kuliah
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $authUser = auth()->user();

        $courses = Course::query()
            ->with('lecturer')
            
            // Filter Keamanan Berdasarkan Peran (Collection-Level IDOR Protection)
            ->when($authUser->role === 'dosen', function ($query) use ($authUser) {
                // Dosen hanya melihat MK miliknya
                $query->where('lecturer_id', $authUser->id);
            })
            ->when($authUser->role === 'mahasiswa', function ($query) use ($authUser) {
                // Mahasiswa hanya melihat MK yang sudah diikutinya
                $query->whereHas('students', function ($q) use ($authUser) {
                    $q->where('users.id', $authUser->id);
                });
            })

            ->when(
                $request->filled('q'),
                fn ($query) =>
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

                    })
            )

            ->when(
                $request->filled('status'),
                fn ($query) =>
                    $query->where(
                        'status',
                        $request->status
                    )
            )

            ->latest()
            ->paginate(15)
            ->withQueryString();



        return view(
            'courses.index',
            compact('courses')
        );

    }





    /*
    |--------------------------------------------------------------------------
    | Detail satu mata kuliah
    |--------------------------------------------------------------------------
    |
    | Route Model Binding:
    | /courses/{course}
    |
    */

    public function show(Course $course)
    {
        // Pengecekan Otorisasi Berdasarkan Policy
        \Illuminate\Support\Facades\Gate::authorize('view', $course);

        $course->load(['lecturer', 'materials.uploader', 'assignments.submissions']);

        $isEnrolled = true; // Ditambahkan agar view blade tidak error undefined variable

        return view('courses.show', compact('course', 'isEnrolled'));
    }

    public function enroll(Course $course)
    {
        $user = auth()->user();
        abort_unless($user->role === 'mahasiswa', 403, 'Hanya mahasiswa yang dapat bergabung dengan mata kuliah.');

        if (!$course->students()->where('users.id', $user->id)->exists()) {
            $course->students()->attach($user->id, ['enrolled_at' => now()]);
        }

        return redirect()->route('mahasiswa.courses.show', $course)
            ->with('success', 'Selamat! Anda berhasil terdaftar di mata kuliah ' . $course->name . '.');
    }

    /*
    |--------------------------------------------------------------------------
    | Kelola Enrollment (Dosen & Admin)
    |--------------------------------------------------------------------------
    */

    public function enrollments(Course $course)
    {
        $authUser = auth()->user();

        // CoursePolicy@update dipakai sebagai proxy untuk kelola enrollment
        Gate::authorize('update', $course);

        $course->load('students');

        // Semua mahasiswa yang belum terdaftar (untuk form tambah)
        $enrolledIds = $course->students->pluck('id');
        $available = User::where('role', 'mahasiswa')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return view('courses.enrollments', compact('course', 'available'));
    }

    public function enrollStore(Request $request, Course $course)
    {
        $authUser = auth()->user();

        Gate::authorize('update', $course);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $student = User::findOrFail($request->user_id);
        abort_unless($student->role === 'mahasiswa', 422, 'Hanya mahasiswa yang bisa didaftarkan.');

        if (!$course->students()->where('users.id', $student->id)->exists()) {
            $course->students()->attach($student->id, ['enrolled_at' => now()]);
        }

        return back()->with('success', $student->name . ' berhasil ditambahkan ke mata kuliah ' . $course->name . '.');
    }

    public function unenroll(Course $course, User $student)
    {
        $authUser = auth()->user();

        Gate::authorize('update', $course);

        $course->students()->detach($student->id);

        return back()->with('success', $student->name . ' berhasil dikeluarkan dari mata kuliah.');
    }





    /*
    |--------------------------------------------------------------------------
    | Form tambah mata kuliah
    |--------------------------------------------------------------------------
    */

    public function create()
    {

        $lecturers = User::where(
            'role',
            'dosen'
        )->get();



        return view(
            'courses.create',
            compact('lecturers')
        );

    }





    /*
    |--------------------------------------------------------------------------
    | Simpan mata kuliah
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreCourseRequest $request
    )
    {


        Course::create(
            $request->validated()
        );



        return redirect()->route(auth()->user()->role . '.courses.index')
            ->with(
                'success',
                'Mata kuliah berhasil ditambahkan.'
            );

    }





    /*
    |--------------------------------------------------------------------------
    | Form edit mata kuliah
    |--------------------------------------------------------------------------
    |
    | IDOR Protection:
    | Dosen hanya boleh edit course miliknya
    | Admin boleh semua
    |
    */

    public function edit(Course $course)
    {
        Gate::authorize('update', $course);

        $lecturers = User::where(
            'role',
            'dosen'
        )->get();


        return view(
            'courses.edit',
            compact(
                'course',
                'lecturers'
            )
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Update mata kuliah
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateCourseRequest $request,
        Course $course
    )
    {
        Gate::authorize('update', $course);



        $course->update(
            $request->validated()
        );



        return redirect()
            ->route(auth()->user()->role . '.courses.show', $course)
            ->with(
                'success',
                'Mata kuliah berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus mata kuliah
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Course $course
    )
    {
        Gate::authorize('delete', $course);



        $course->delete();



        return redirect()->route(auth()->user()->role . '.courses.index')
            ->with(
                'success',
                'Mata kuliah berhasil dihapus.'
            );

    }


}