<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Menyediakan accessor `formatted_file_size` untuk model yang punya kolom
 * `file_size` (dalam byte), mis. Material dan Submission.
 *
 * Contoh keluaran: "512 B", "320.5 KB", "1.45 MB".
 *
 * Dibuat sendiri (bukan Illuminate\Support\Number::fileSize) agar tidak
 * bergantung pada ekstensi PHP `intl` yang belum tentu aktif di server.
 */
trait HasFormattedFileSize
{
    protected function formattedFileSize(): Attribute
    {
        return Attribute::get(fn () => static::humanFileSize($this->file_size));
    }

    /**
     * Ubah byte menjadi teks yang terbaca manusia.
     * Mengembalikan null bila ukuran tidak diketahui (kolom kosong).
     */
    public static function humanFileSize(?int $bytes): ?string
    {
        if ($bytes === null || $bytes < 0) {
            return null;
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return number_format($bytes / (1024 * 1024), 2) . ' MB';
    }
}
