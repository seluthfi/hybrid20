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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('starting_weight_kg', 5, 2)->nullable();
            $table->decimal('target_weight_kg', 5, 2)->nullable();
            $table->unsignedInteger('daily_calorie_target')->nullable();
            $table->unsignedInteger('protein_target_g')->nullable();
            $table->unsignedInteger('carbs_target_g')->nullable();
            $table->unsignedInteger('fat_target_g')->nullable();
            $table->unsignedInteger('daily_steps_target')->default(10000);
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('weight_unit', 10)->default('kg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
