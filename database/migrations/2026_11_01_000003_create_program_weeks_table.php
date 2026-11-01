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
        Schema::create('program_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('week_number');
            $table->unsignedInteger('calendar_position');
            $table->boolean('is_repeat')->default(false);
            $table->string('phase_label', 20)->nullable(); // deload|taper
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'calendar_position']);
            $table->index(['program_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_weeks');
    }
};
