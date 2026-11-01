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
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scheduled_workout_id')->nullable()->constrained()->nullOnDelete();
            $table->string('workout_type', 20); // gym|run|other
            $table->string('title');
            $table->dateTime('performed_at'); // UTC
            $table->date('performed_on'); // Lokal
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->unsignedInteger('pace_seconds_per_km')->nullable();
            $table->unsignedInteger('steps')->nullable();
            $table->unsignedTinyInteger('rpe')->nullable(); // 1-10
            $table->string('status', 20)->default('completed'); // completed|partial|skipped
            $table->string('body_condition', 20)->nullable(); // segar|normal|lelah
            $table->boolean('pain_flag')->default(false);
            $table->text('pain_notes')->nullable();
            $table->text('general_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'performed_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
