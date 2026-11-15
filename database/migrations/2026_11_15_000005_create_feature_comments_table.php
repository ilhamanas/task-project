<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('isi');
            $table->timestamps();

            $table->index(['feature_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_comments');
    }
};
