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
        $admin = User::create([
            'name'  => 'Admin Utama',
            'email' => 'admin@kampuslms.test',
            'password' => 'password',
        ]);
        $admin->role = 'admin';
        $admin->save();

        // =============================================
        // 2. BUAT DOSEN (3 akun)
        // =============================================
        $dosenList = [];
        for ($i = 1; $i <= 3; $i++) {
            $dosen = User::create([
                'name'    => "Dosen $i",
                'email'   => $i === 1 ? 'dosen@kampuslms.test' : "dosen$i@kampuslms.test",
                'password' => 'password',
                'nim_nip' => "NIP00$i",
            ]);
            $dosen->role = 'dosen';
            $dosen->save();
            $dosenList[] = $dosen;
        }

        // =============================================
        // 3. BUAT MAHASISWA (30 akun)
        // =============================================
        $mahasiswaList = [];
        for ($i = 1; $i <= 30; $i++) {
            $mhs = User::create([
                'name'    => "Mahasiswa $i",
                'email'   => $i === 1 ? 'mahasiswa@kampuslms.test' : "mhs$i@kampuslms.test",
                'password' => 'password',
                'nim_nip' => "NIM00$i",
            ]);
            $mhs->role = 'mahasiswa';
            $mhs->save();
            $mahasiswaList[] = $mhs;
        }

        // =============================================
        // 4. BUAT 5 MATA KULIAH + ENROLL MAHASISWA
        // =============================================
        $coursesList = [];
        for ($i = 1; $i <= 5; $i++) {
            $course = Course::create([
                'code'        => "SI251402$i",
                'name'        => "Mata Kuliah $i",
                'description' => "Deskripsi lengkap mata kuliah $i.",
                'sks'         => 3,
                'lecturer_id' => $dosenList[($i - 1) % 3]->id,
                'status'      => 'active',
            ]);

            // Enroll 20 mahasiswa (> minimum 15 dari spesifikasi)
            $shuffled = $mahasiswaList;
            shuffle($shuffled);
            $enrolled = array_slice($shuffled, 0, 20);
            foreach ($enrolled as $mhs) {
                $course->students()->attach($mhs->id, ['enrolled_at' => Carbon::now()]);
            }

            $coursesList[] = $course;
        }

        // =============================================
        // 5. BUAT TUGAS + SUBMISSION + NILAI
        // =============================================
        foreach ($coursesList as $course) {
            $assignments = [];

            // Tugas 1 - Draft (tidak bisa dikumpul)
            $assignments[] = Assignment::create([
                'course_id'    => $course->id,
                'created_by'   => $course->lecturer_id,
                'title'        => "[{$course->code}] Tugas 1 - Pendahuluan",
                'instructions' => 'Buat makalah singkat tentang topik pertemuan pertama.',
                'due_at'       => Carbon::now()->addDays(7),
                'status'       => 'draft',
            ]);

            // Tugas 2 - Published (aktif)
            $assignments[] = Assignment::create([
                'course_id'    => $course->id,
                'created_by'   => $course->lecturer_id,
                'title'        => "[{$course->code}] Tugas 2 - Analisis",
                'instructions' => 'Kerjakan soal analisis pada modul bab 2.',
                'due_at'       => Carbon::now()->addDays(3),
                'status'       => 'published',
            ]);

            // Tugas 3 - Published, sudah lewat deadline
            $assignments[] = Assignment::create([
                'course_id'    => $course->id,
                'created_by'   => $course->lecturer_id,
                'title'        => "[{$course->code}] Tugas 3 - Implementasi",
                'instructions' => 'Implementasikan konsep yang telah dipelajari dalam sebuah proyek kecil.',
                'due_at'       => Carbon::now()->subDays(3),
                'status'       => 'published',
            ]);

            // Iterasi submission per tugas yang published
            $enrolledStudents = $course->students()->get();

            foreach ($assignments as $assignment) {
                if ($assignment->status === 'draft') continue;

                foreach ($enrolledStudents as $student) {
                    // 80% mahasiswa mengumpulkan
                    if (rand(1, 100) > 80) continue;

                    $isLate = Carbon::now()->gt($assignment->due_at);

                    $submission = Submission::create([
                        'assignment_id' => $assignment->id,
                        'user_id'       => $student->id,
                        'file_path'     => "submissions/tugas_{$assignment->id}_{$student->id}.pdf",
                        'original_name' => "Tugas_{$student->name}.pdf",
                        'file_size'     => rand(102400, 5120000),
                        'note'          => 'Mohon diperiksa, terima kasih.',
                        'submitted_at'  => Carbon::now(),
                        'is_late'       => $isLate,
                    ]);

                    // 60% submission sudah dinilai
                    if (rand(1, 100) <= 60) {
                        Grade::create([
                            'submission_id' => $submission->id,
                            'graded_by'     => $course->lecturer_id,
                            'score'         => rand(60, 100),
                            'feedback'      => 'Hasil cukup baik, pertahankan!',
                            'graded_at'     => Carbon::now(),
                        ]);
                    }
                }
            }
        }
    }
}
