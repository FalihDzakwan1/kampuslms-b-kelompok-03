<?php

namespace App\Models;

use App\Models\Concerns\HasFormattedFileSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory, HasFormattedFileSize;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'original_name',
        'file_size',
        'note',
        'submitted_at',
        'is_late',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'submitted_at' => 'datetime',
            'is_late' => 'boolean',
        ];
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function grade()
    {
        return $this->hasOne(Grade::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Submission $submission) {
            if ($submission->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($submission->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($submission->file_path);
            }
        });
    }
}
