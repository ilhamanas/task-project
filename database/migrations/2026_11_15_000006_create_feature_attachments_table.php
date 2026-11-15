<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_file');
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->timestamps();

            $table->index('feature_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_attachments');
    }
};
