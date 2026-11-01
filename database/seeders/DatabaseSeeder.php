<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use App\Services\ProgramGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Master Latihan Gym Default
        $this->call(DefaultExerciseSeeder::class);

        // 2. Buat Pengguna Utama
        $user = User::firstOrCreate(
            ['email' => 'atlet@hybrid20.id'],
            [
                'name' => 'Luthfi',
                'password' => Hash::make('password123'),
            ]
        );

        // 3. Buat Nilai Awal Pengaturan (§6.3)
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'height_cm' => 163.0,
                'starting_weight_kg' => 74.00,
                'target_weight_kg' => 60.00,
                'daily_calorie_target' => 2200,
                'protein_target_g' => 140,
                'carbs_target_g' => 260,
                'fat_target_g' => 65,
                'daily_steps_target' => 10000,
                'timezone' => 'Asia/Jakarta',
                'weight_unit' => 'kg',
            ]
        );

        // 4. Buat Program 20 Minggu Penuh Mulai Hari Senin (§8.1)
        if ($user->programs()->doesntExist()) {
            $generator = new ProgramGenerator();
            $generator->generate($user, '2026-11-02', 20, 'HYBRID 20 Program');
        }
    }
}
