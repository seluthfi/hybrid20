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
        Schema::create('scheduled_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_week_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1=Senin .. 7=Minggu
            $table->string('workout_type', 20); // gym|run|rest
            $table->string('title');
            $table->decimal('planned_distance_km', 6, 2)->nullable();
            $table->unsignedInteger('planned_duration_minutes')->nullable();
            $table->string('intensity_label', 30)->nullable(); // easy|long_run|taper_or_test|steady_optional
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->text('instructions')->nullable();
            $table->string('status', 20)->default('planned'); // planned|completed|partial|skipped|rescheduled
            $table->date('rescheduled_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_workouts');
    }
};
