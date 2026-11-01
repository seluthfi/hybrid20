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
        Schema::create('exercise_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('set_number');
            $table->unsignedInteger('reps')->nullable();
            $table->decimal('weight_kg', 7, 2)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('distance_m', 8, 2)->nullable();
            $table->unsignedTinyInteger('rpe')->nullable(); // 1-10
            $table->boolean('is_warmup')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_sets');
    }
};
