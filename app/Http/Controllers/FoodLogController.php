<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodLog;
use App\Models\UserSetting;
use App\Support\Num;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FoodLogController extends Controller
{
    /**
     * Display food diary for a specific date (PLAN.md §10.8).
     * GET /food-logs?date=
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        $timezone = $settings?->timezone ?? 'Asia/Jakarta';

        $dateParam = $request->query('date');
        $date = $dateParam ? Carbon::parse($dateParam, $timezone)->startOfDay() : Carbon::now($timezone)->startOfDay();
        $dateString = $date->toDateString();

        // Fetch logs for this date
        $logs = FoodLog::where('user_id', $user->id)
            ->whereDate('logged_on', $dateString)
            ->orderBy('logged_at')
            ->get();

        // Group by meal_type: breakfast, lunch, dinner, snack
        $mealTypes = [
            'breakfast' => 'Sarapan',
            'lunch' => 'Makan Siang',
            'dinner' => 'Makan Malam',
            'snack' => 'Camilan',
        ];

        $meals = [];
        $totalCalories = 0;
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;

        foreach ($mealTypes as $key => $title) {
            $items = $logs->where('meal_type', $key)->values();

            $subCalories = (float) $items->sum('calories_kcal');
            $subProtein = (float) $items->sum('protein_g');
            $subCarbs = (float) $items->sum('carbs_g');
            $subFat = (float) $items->sum('fat_g');

            $meals[$key] = [
                'key' => $key,
                'title' => $title,
                'items' => $items,
                'calories' => $subCalories,
                'protein' => $subProtein,
                'carbs' => $subCarbs,
                'fat' => $subFat,
            ];

            $totalCalories += $subCalories;
            $totalProtein += $subProtein;
            $totalCarbs += $subCarbs;
            $totalFat += $subFat;
        }

        // Targets from settings
        $targetCalories = $settings?->daily_calorie_target ?? 2200;
        $targetProtein = $settings?->protein_target_g ?? 140;
        $targetCarbs = $settings?->carbs_target_g ?? 260;
        $targetFat = $settings?->fat_target_g ?? 65;

        // Energy remaining / over target status
        $isOverCalorie = $totalCalories > $targetCalories;
        $calorieDiff = abs($totalCalories - $targetCalories);
        $caloriePercent = $targetCalories > 0 ? min(100, round(($totalCalories / $targetCalories) * 100, 1)) : 0;

        $proteinPercent = $targetProtein > 0 ? min(100, round(($totalProtein / $targetProtein) * 100, 1)) : 0;
        $carbsPercent = $targetCarbs > 0 ? min(100, round(($totalCarbs / $targetCarbs) * 100, 1)) : 0;
        $fatPercent = $targetFat > 0 ? min(100, round(($totalFat / $targetFat) * 100, 1)) : 0;

        return view('food_logs.index', compact(
            'date',
            'dateString',
            'meals',
            'totalCalories',
            'totalProtein',
            'totalCarbs',
            'totalFat',
            'targetCalories',
            'targetProtein',
            'targetCarbs',
            'targetFat',
            'isOverCalorie',
            'calorieDiff',
            'caloriePercent',
            'proteinPercent',
            'carbsPercent',
            'fatPercent'
        ));
    }

    /**
     * Show form to search and add food to diary (PLAN.md §10.8).
     * GET /food-logs/create?meal_type=&date=
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        $timezone = $settings?->timezone ?? 'Asia/Jakarta';

        $mealType = $request->query('meal_type', 'breakfast');
        $date = $request->query('date', Carbon::now($timezone)->toDateString());

        return view('food_logs.create', compact('mealType', 'date'));
    }

    /**
     * Store food log with calculated macro snapshots (PLAN.md §8.7).
     * POST /food-logs
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        $timezone = $settings?->timezone ?? 'Asia/Jakarta';

        if ($request->has('servings')) {
            $request->merge(['servings' => \App\Support\Num::toDecimal($request->input('servings'))]);
        }

        $validated = $request->validate([
            'food_id' => ['required', 'exists:foods,id'],
            'meal_type' => ['required', Rule::in(['breakfast', 'lunch', 'dinner', 'snack'])],
            'logged_on' => ['required', 'date_format:Y-m-d'],
            'servings' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $food = Food::where('user_id', $user->id)->findOrFail($validated['food_id']);
        $servings = (float) $validated['servings'];

        // Calculate UTC logged_at timestamp based on user's timezone date
        $now = Carbon::now($timezone);
        $localDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $validated['logged_on'] . ' ' . $now->format('H:i:s'), $timezone);
        $utcLoggedAt = $localDateTime->copy()->setTimezone('UTC');

        // Macro snapshots = master * servings (PLAN.md §8.7)
        FoodLog::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'logged_at' => $utcLoggedAt,
            'logged_on' => $validated['logged_on'],
            'meal_type' => $validated['meal_type'],
            'food_name_snapshot' => $food->name,
            'serving_name_snapshot' => $food->serving_name,
            'serving_size_g_snapshot' => $food->serving_size_g,
            'servings' => $servings,
            'calories_kcal' => round((float) $food->calories_kcal * $servings, 2),
            'protein_g' => round((float) $food->protein_g * $servings, 2),
            'carbs_g' => round((float) $food->carbs_g * $servings, 2),
            'fat_g' => round((float) $food->fat_g * $servings, 2),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('food-logs.index', ['date' => $validated['logged_on']])->with('toast', [
            'type' => 'success',
            'message' => "{$food->name} berhasil dicatat ke jurnal makan.",
        ]);
    }

    /**
     * Update food log servings / meal_type.
     * PUT /food-logs/{id}
     */
    public function update(Request $request, FoodLog $foodLog): RedirectResponse
    {
        abort_unless($foodLog->user_id === Auth::id(), 403);

        if ($request->has('servings')) {
            $request->merge(['servings' => \App\Support\Num::toDecimal($request->input('servings'))]);
        }

        $validated = $request->validate([
            'servings' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'meal_type' => ['nullable', Rule::in(['breakfast', 'lunch', 'dinner', 'snack'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newServings = (float) $validated['servings'];

        // Recalculate macro snapshot proportionally to old servings
        $ratio = $foodLog->servings > 0 ? ($newServings / (float) $foodLog->servings) : 1;

        $foodLog->update([
            'servings' => $newServings,
            'meal_type' => $validated['meal_type'] ?? $foodLog->meal_type,
            'calories_kcal' => round((float) $foodLog->calories_kcal * $ratio, 2),
            'protein_g' => round((float) $foodLog->protein_g * $ratio, 2),
            'carbs_g' => round((float) $foodLog->carbs_g * $ratio, 2),
            'fat_g' => round((float) $foodLog->fat_g * $ratio, 2),
            'notes' => $validated['notes'] ?? $foodLog->notes,
        ]);

        return redirect()->route('food-logs.index', ['date' => $foodLog->logged_on->toDateString()])->with('toast', [
            'type' => 'success',
            'message' => 'Porsi makanan berhasil diperbarui.',
        ]);
    }

    /**
     * Delete food log.
     * DELETE /food-logs/{id}
     */
    public function destroy(FoodLog $foodLog): RedirectResponse
    {
        abort_unless($foodLog->user_id === Auth::id(), 403);

        $dateString = $foodLog->logged_on->toDateString();
        $foodLog->delete();

        return redirect()->route('food-logs.index', ['date' => $dateString])->with('toast', [
            'type' => 'success',
            'message' => 'Catatan makanan berhasil dihapus.',
        ]);
    }
}
