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

        $courses = Course::query()
            ->with('lecturer')

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

        $course->load('lecturer');


        return view(
            'courses.show',
            compact('course')
        );

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



        return redirect()
            ->route(
                'dosen.courses.index'
            )
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

        dd([
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role,
            'course_id' => $course->id,
            'course_lecturer_id' => $course->lecturer_id,
        ]);


        abort_unless(

            $course->lecturer_id === auth()->id()
            ||
            auth()->user()->role === 'admin',

            403

        );

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


        abort_unless(

            $course->lecturer_id === auth()->id()
            ||
            auth()->user()->role === 'admin',

            403

        );



        $course->update(
            $request->validated()
        );



        return redirect()
            ->route('dosen.courses.show', $course)
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


        abort_unless(

            $course->lecturer_id === auth()->id()
            ||
            auth()->user()->role === 'admin',

            403

        );



        $course->delete();



        return redirect()
            ->route(
                'dosen.courses.index'
            )
            ->with(
                'success',
                'Mata kuliah berhasil dihapus.'
            );

    }


}