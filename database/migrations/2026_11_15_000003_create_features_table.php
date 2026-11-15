<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('belum_mulai');
            $table->string('prioritas', 20)->default('sedang');
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tgl_target')->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->text('catatan_gagal')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'status', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
