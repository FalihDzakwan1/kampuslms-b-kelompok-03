<?php

namespace App\Models;

use App\Models\Concerns\HasFormattedFileSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory, HasFormattedFileSize;

    protected $fillable = [
        'course_id',
        'uploaded_by',
        'title',
        'description',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'external_url',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    protected static function booted(): void
    {
        static::deleting(function (Material $material) {
            if ($material->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($material->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($material->file_path);
            }
        });
    }
}
