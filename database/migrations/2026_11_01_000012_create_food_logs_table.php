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
        Schema::create('food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->nullable()->constrained('foods')->nullOnDelete();
            $table->dateTime('logged_at'); // UTC
            $table->date('logged_on'); // Lokal
            $table->string('meal_type', 20); // breakfast|lunch|dinner|snack
            $table->string('food_name_snapshot');
            $table->string('serving_name_snapshot', 50);
            $table->decimal('serving_size_g_snapshot', 8, 2);
            $table->decimal('servings', 8, 2);
            $table->decimal('calories_kcal', 8, 2);
            $table->decimal('protein_g', 8, 2);
            $table->decimal('carbs_g', 8, 2);
            $table->decimal('fat_g', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'logged_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_logs');
    }
};
