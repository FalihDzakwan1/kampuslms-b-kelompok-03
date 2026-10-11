<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    /**
     * GET /api/v1/courses
     * Auth — dosen: MK yang diajar; mahasiswa: MK yang diikuti; admin: semua
     * Eager loading: lecturer
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Course::query()
            ->with('lecturer')
            ->when($user->role === 'dosen', fn ($q) =>
                $q->where('lecturer_id', $user->id)
            )
            ->when($user->role === 'mahasiswa', fn ($q) =>
                $q->whereHas('students', fn ($sub) =>
                    $sub->where('users.id', $user->id)
                )
            );

        return CourseResource::collection($query->paginate($request->get('per_page', 15)));
    }

    /**
     * GET /api/v1/courses/{id}
     * Auth + scope — detail + jumlah materi/tugas
     * Eager loading: lecturer, withCount materials & assignments
     */
    public function show(Request $request, Course $course)
    {
        // Pengecekan Otorisasi Berdasarkan Policy
        Gate::authorize('view', $course);

        $course->loadCount(['materials', 'assignments']);
        $course->load('lecturer');

        return new CourseResource($course);
    }

    /**
     * GET /api/v1/courses/{id}/materials
     * Auth + scope
     * Eager loading: uploader
     */
    public function materials(Request $request, Course $course)
    {
        Gate::authorize('viewAny', [Material::class, $course]);

        $materials = $course->materials()
            ->with('uploader')
            ->paginate($request->get('per_page', 15));

        return MaterialResource::collection($materials);
    }

    /**
     * GET /api/v1/courses/{id}/assignments
     * Auth + scope — mendukung ?status= dan ?page=
     * Eager loading: creator
     */
    public function assignments(Request $request, Course $course)
    {
        Gate::authorize('viewAny', [Assignment::class, $course]);

        $user = $request->user();

        $query = $course->assignments()
            ->with('creator')
            ->when($user->role === 'mahasiswa', fn ($q) =>
                $q->where('status', 'published')
            )
            ->when($user->role !== 'mahasiswa' && $request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            );

        return AssignmentResource::collection($query->paginate($request->get('per_page', 15)));
    }
}