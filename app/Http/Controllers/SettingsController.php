<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\UserSetting;
use App\Services\ProgramDateService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected ProgramDateService $dateService
    ) {}

    /**
     * Show settings page.
     * GET /settings
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'height_cm' => 163,
                'starting_weight_kg' => 74.0,
                'target_weight_kg' => 60.0,
                'daily_calorie_target' => 2200,
                'protein_target_g' => 140,
                'carbs_target_g' => 260,
                'fat_target_g' => 65,
                'daily_steps_target' => 10000,
                'timezone' => 'Asia/Jakarta',
                'weight_unit' => 'kg',
            ]
        );

        $program = Program::where('user_id', $user->id)->first();

        return view('settings.index', [
            'settings' => $settings,
            'program' => $program,
        ]);
    }

    /**
     * Update user targets and physical settings.
     * PUT/PATCH /settings
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $settings = UserSetting::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'height_cm' => ['nullable', 'numeric', 'min:50', 'max:250'],
            'starting_weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'target_weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'daily_steps_target' => ['required', 'integer', 'min:1000', 'max:50000'],
            'daily_calorie_target' => ['required', 'integer', 'min:500', 'max:10000'],
            'protein_target_g' => ['required', 'integer', 'min:10', 'max:500'],
            'carbs_target_g' => ['required', 'integer', 'min:10', 'max:1000'],
            'fat_target_g' => ['required', 'integer', 'min:10', 'max:500'],
        ]);

        $settings->update($validated);

        return back()->with('success', 'Pengaturan target berhasil diperbarui.');
    }

    /**
     * Update program start date (must be a Monday).
     * PUT /settings/program
     */
    public function updateProgram(Request $request): RedirectResponse
    {
        $user = $request->user();
        $program = Program::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();

        if (! $startDate->isMonday()) {
            return back()->withErrors(['start_date' => 'Tanggal mulai harus hari Senin.']);
        }

        $program->update([
            'start_date' => $startDate->toDateString(),
        ]);

        return back()->with('success', 'Tanggal mulai program berhasil diubah.');
    }
}
