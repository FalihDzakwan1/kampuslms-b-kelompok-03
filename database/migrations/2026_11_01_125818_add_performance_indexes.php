<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        // 1. submissions.is_late — filter keterlambatan
        Schema::table('submissions', function (Blueprint $table) {
            $table->index('is_late', 'idx_submissions_is_late');
        });

        // 2. notifications — polling notifikasi belum dibaca (query paling sering di app)
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(
                ['notifiable_id', 'notifiable_type', 'read_at'],
                'idx_notifications_notifiable_read'
            );
        });

        // 3. courses.status — filter status MK di daftar
        Schema::table('courses', function (Blueprint $table) {
            $table->index('status', 'idx_courses_status');
        });

        // 4. materials.course_id + created_at — daftar materi per MK diurutkan terbaru
        Schema::table('materials', function (Blueprint $table) {
            $table->index(['course_id', 'created_at'], 'idx_materials_course_created');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submissions_is_late');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_notifiable_read');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_status');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex('idx_materials_course_created');
        });
    }
};
