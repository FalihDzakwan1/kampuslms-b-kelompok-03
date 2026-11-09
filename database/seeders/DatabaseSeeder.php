<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Grade;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // 1. BUAT ADMIN (1 akun)
        // =============================================
        $admin = User::withTrashed()->firstOrNew(['email' => 'admin@kampuslms.test']);
        $admin->forceFill([
            'name'       => 'Admin Utama',
            'role'       => 'admin',
            'password'   => 'password',
            'deleted_at' => null,
        ])->save();

        // =============================================
        // 2. BUAT DOSEN (3 akun)
        // =============================================
        $dosenList = [];
        for ($i = 1; $i <= 3; $i++) {
            $email = $i === 1 ? 'dosen@kampuslms.test' : "dosen$i@kampuslms.test";
            $dosen = User::withTrashed()->firstOrNew(['email' => $email]);
            $dosen->forceFill([
                'name'       => "Dosen $i",
                'role'       => 'dosen',
                'nim_nip'    => "NIP00$i",
                'password'   => 'password',
                'deleted_at' => null,
            ])->save();
            $dosenList[] = $dosen;
        }

        // =============================================
        // 3. BUAT MAHASISWA (30 akun)
        // =============================================
        $mahasiswaList = [];
        for ($i = 1; $i <= 30; $i++) {
            $email = $i === 1 ? 'mahasiswa@kampuslms.test' : "mhs$i@kampuslms.test";
            $mhs = User::withTrashed()->firstOrNew(['email' => $email]);
            $mhs->forceFill([
                'name'       => "Mahasiswa $i",
                'role'       => 'mahasiswa',
                'nim_nip'    => "NIM00$i",
                'password'   => 'password',
                'deleted_at' => null,
            ])->save();
            $mahasiswaList[] = $mhs;
        }

        // =============================================
        // 4. BUAT 5 MATA KULIAH + ENROLL MAHASISWA
        // =============================================
        $coursesList = [];
        for ($i = 1; $i <= 5; $i++) {
            $course = Course::firstOrCreate(
                ['code' => "SI251402$i"],
                [
                    'name'        => "Mata Kuliah $i",
                    'description' => "Deskripsi lengkap mata kuliah $i.",
                    'sks'         => 3,
                    'lecturer_id' => $dosenList[($i - 1) % 3]->id,
                    'status'      => 'active',
                ]
            );

            // Enroll mahasiswa (pastikan minimal 20 mahasiswa ter-enroll)
            $existingStudentIds = $course->students()->pluck('users.id')->toArray();
            $needed = 20 - count($existingStudentIds);

            if ($needed > 0) {
                $candidateIds = array_diff(
                    array_map(fn($m) => $m->id, $mahasiswaList),
                    $existingStudentIds
                );
                shuffle($candidateIds);
                $toEnroll = array_slice($candidateIds, 0, $needed);

                $syncData = [];
                foreach ($toEnroll as $studentId) {
                    $syncData[$studentId] = ['enrolled_at' => Carbon::now()];
                }
                $course->students()->syncWithoutDetaching($syncData);
            }

            $coursesList[] = $course;
        }

        // =============================================
        // 5. BUAT TUGAS + SUBMISSION + NILAI
        // =============================================
        foreach ($coursesList as $course) {
            $assignments = [];

            $assignmentDefs = [
                // Tugas 1 - Draft (tidak bisa dikumpul)
                [
                    'title'        => "[{$course->code}] Tugas 1 - Pendahuluan",
                    'instructions' => 'Buat makalah singkat tentang topik pertemuan pertama.',
                    'due_at'       => Carbon::now()->addDays(7),
                    'status'       => 'draft',
                ],
                // Tugas 2 - Published (aktif)
                [
                    'title'        => "[{$course->code}] Tugas 2 - Analisis",
                    'instructions' => 'Kerjakan soal analisis pada modul bab 2.',
                    'due_at'       => Carbon::now()->addDays(3),
                    'status'       => 'published',
                ],
                // Tugas 3 - Published, sudah lewat deadline
                [
                    'title'        => "[{$course->code}] Tugas 3 - Implementasi",
                    'instructions' => 'Implementasikan konsep yang telah dipelajari dalam sebuah proyek kecil.',
                    'due_at'       => Carbon::now()->subDays(3),
                    'status'       => 'published',
                ],
            ];

            foreach ($assignmentDefs as $def) {
                $assignment = Assignment::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'title'     => $def['title'],
                    ],
                    [
                        'created_by'   => $course->lecturer_id,
                        'instructions' => $def['instructions'],
                        'due_at'       => $def['due_at'],
                        'status'       => $def['status'],
                    ]
                );
                $assignments[] = $assignment;
            }

            // Iterasi submission per tugas yang published
            $enrolledStudents = $course->students()->get();

            foreach ($assignments as $assignment) {
                if ($assignment->status === 'draft') continue;

                foreach ($enrolledStudents as $student) {
                    // Cari submission yang sudah ada atau buat jika belum ada
                    $submission = Submission::where('assignment_id', $assignment->id)
                        ->where('user_id', $student->id)
                        ->first();

                    if (!$submission) {
                        // 80% mahasiswa mengumpulkan
                        if (rand(1, 100) > 80) continue;

                        $isLate = Carbon::now()->gt($assignment->due_at);

                        $submission = Submission::firstOrCreate(
                            [
                                'assignment_id' => $assignment->id,
                                'user_id'       => $student->id,
                            ],
                            [
                                'file_path'     => "submissions/tugas_{$assignment->id}_{$student->id}.pdf",
                                'original_name' => "Tugas_{$student->name}.pdf",
                                'file_size'     => rand(102400, 5120000),
                                'note'          => 'Mohon diperiksa, terima kasih.',
                                'submitted_at'  => Carbon::now(),
                                'is_late'       => $isLate,
                            ]
                        );
                    }

                    // Buat grade jika submission belum memiliki nilai
                    if ($submission && !$submission->grade()->exists()) {
                        // 60% submission sudah dinilai
                        if (rand(1, 100) <= 60) {
                            Grade::firstOrCreate(
                                ['submission_id' => $submission->id],
                                [
                                    'graded_by'     => $course->lecturer_id,
                                    'score'         => rand(60, 100),
                                    'feedback'      => 'Hasil cukup baik, pertahankan!',
                                    'graded_at'     => Carbon::now(),
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
