<?php

namespace Database\Seeders;

class RunningPlanSeeder
{
    /**
     * Data jarak lari per sesi (km) dan phase_label untuk 20 minggu persis sesuai PLAN.md §6.1.
     */
    public static function data(): array
    {
        return [
            1  => ['tue' => 2.0, 'fri' => 2.0, 'sun' => 2.5,  'total' => 6.5,  'phase_label' => null],
            2  => ['tue' => 2.5, 'fri' => 2.0, 'sun' => 3.0,  'total' => 7.5,  'phase_label' => null],
            3  => ['tue' => 2.5, 'fri' => 2.5, 'sun' => 3.5,  'total' => 8.5,  'phase_label' => null],
            4  => ['tue' => 2.0, 'fri' => 2.0, 'sun' => 3.0,  'total' => 7.0,  'phase_label' => 'deload'],
            5  => ['tue' => 3.0, 'fri' => 2.5, 'sun' => 4.0,  'total' => 9.5,  'phase_label' => null],
            6  => ['tue' => 3.0, 'fri' => 3.0, 'sun' => 4.5,  'total' => 10.5, 'phase_label' => null],
            7  => ['tue' => 3.5, 'fri' => 3.0, 'sun' => 5.0,  'total' => 11.5, 'phase_label' => null],
            8  => ['tue' => 2.5, 'fri' => 2.5, 'sun' => 4.0,  'total' => 9.0,  'phase_label' => 'deload'],
            9  => ['tue' => 3.5, 'fri' => 3.0, 'sun' => 5.5,  'total' => 12.0, 'phase_label' => null],
            10 => ['tue' => 4.0, 'fri' => 3.0, 'sun' => 6.0,  'total' => 13.0, 'phase_label' => null],
            11 => ['tue' => 4.0, 'fri' => 3.5, 'sun' => 6.5,  'total' => 14.0, 'phase_label' => null],
            12 => ['tue' => 3.0, 'fri' => 3.0, 'sun' => 5.0,  'total' => 11.0, 'phase_label' => 'deload'],
            13 => ['tue' => 4.0, 'fri' => 3.5, 'sun' => 7.0,  'total' => 14.5, 'phase_label' => null],
            14 => ['tue' => 4.0, 'fri' => 4.0, 'sun' => 7.5,  'total' => 15.5, 'phase_label' => null],
            15 => ['tue' => 4.5, 'fri' => 4.0, 'sun' => 8.0,  'total' => 16.5, 'phase_label' => null],
            16 => ['tue' => 3.5, 'fri' => 3.0, 'sun' => 6.0,  'total' => 12.5, 'phase_label' => 'deload'],
            17 => ['tue' => 4.5, 'fri' => 4.0, 'sun' => 8.5,  'total' => 17.0, 'phase_label' => null],
            18 => ['tue' => 5.0, 'fri' => 4.0, 'sun' => 9.0,  'total' => 18.0, 'phase_label' => null],
            19 => ['tue' => 3.5, 'fri' => 3.0, 'sun' => 7.0,  'total' => 13.5, 'phase_label' => 'deload'],
            20 => ['tue' => 3.0, 'fri' => 2.0, 'sun' => 10.0, 'total' => 15.0, 'phase_label' => 'taper'],
        ];
    }
}
