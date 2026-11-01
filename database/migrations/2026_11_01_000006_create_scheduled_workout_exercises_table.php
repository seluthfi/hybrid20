<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_workout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->string('exercise_name');
            $table->unsignedInteger('target_sets');
            $table->unsignedInteger('target_reps')->nullable();
            $table->unsignedInteger('target_duration_seconds')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_workout_exercises');
    }
};
