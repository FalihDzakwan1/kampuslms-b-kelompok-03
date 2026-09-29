<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nim_nip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Dosen - mengajar mata kuliah
    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }

    // Mahasiswa - terdaftar di mata kuliah
    public function courses()
    {
        return $this->belongsToMany(Course::class)->withPivot('enrolled_at')->withTimestamps();
    }

    // Mahasiswa - mengumpulkan tugas
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // Dosen/Admin - mengunggah materi
    public function materials()
    {
        return $this->hasMany(Material::class, 'uploaded_by');
    }

    // Dosen - memberi nilai
    public function gradesGiven()
    {
        return $this->hasMany(Grade::class, 'graded_by');
    }
}
