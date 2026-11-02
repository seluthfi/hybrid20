<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodLog;
use App\Support\Num;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FoodController extends Controller
{
    /**
     * Display list of custom foods.
     * GET /foods
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $foods = Food::where('user_id', $user->id)
            ->orderByDesc('is_favorite')
            ->orderBy('name')
            ->paginate(20);

        return view('foods.index', compact('foods'));
    }

    /**
     * Search foods via JSON endpoint (D17, PLAN.md §10.8).
     * GET /foods/search?q=&filter=
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $request->query('q', '');
        $filter = $request->query('filter', '');

        $builder = Food::where('user_id', $user->id);

        if ($filter === 'favorite') {
            $builder->where('is_favorite', true);
        } elseif ($filter === 'recent') {
            // Get food IDs from recent logs
            $recentFoodIds = FoodLog::where('user_id', $user->id)
                ->whereNotNull('food_id')
                ->latest('logged_at')
                ->limit(30)
                ->pluck('food_id')
                ->unique()
                ->toArray();

            $builder->whereIn('id', $recentFoodIds);
        }

        if (! empty($query)) {
            $builder->where('name', 'like', "%{$query}%");
        }

        $foods = $builder->orderByDesc('is_favorite')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($foods);
    }

    /**
     * Show form to create new food.
     * GET /foods/create
     */
    public function create(): View
    {
        return view('foods.create');
    }

    /**
     * Store new food master.
     * POST /foods
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $fields = ['serving_size_g', 'calories_kcal', 'protein_g', 'carbs_g', 'fat_g'];
        $raw = [];
        foreach ($fields as $field) {
            if ($request->has($field) && $request->filled($field)) {
                $raw[$field] = \App\Support\Num::toDecimal($request->input($field));
            }
        }
        if (! empty($raw)) {
            $request->merge($raw);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'serving_name' => ['nullable', 'string', 'max:100'],
            'serving_size_g' => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
            'calories_kcal' => ['required', 'numeric', 'min:0', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'carbs_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'is_favorite' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $food = Food::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'serving_name' => $validated['serving_name'] ?? '1 porsi',
            'serving_size_g' => $validated['serving_size_g'] ?? 100,
            'calories_kcal' => $validated['calories_kcal'],
            'protein_g' => $validated['protein_g'] ?? 0,
            'carbs_g' => $validated['carbs_g'] ?? 0,
            'fat_g' => $validated['fat_g'] ?? 0,
            'is_favorite' => (bool) ($request->has('is_favorite') && $request->input('is_favorite')),
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json($food, 201);
        }

        return redirect()->route('foods.index')->with('toast', [
            'type' => 'success',
            'message' => 'Makanan baru berhasil ditambahkan.',
        ]);
    }

    /**
     * Show form to edit food.
     * GET /foods/{id}/edit
     */
    public function edit(Food $food): View
    {
        abort_unless($food->user_id === Auth::id(), 403);

        return view('foods.edit', compact('food'));
    }

    /**
     * Update food master.
     * PUT /foods/{id}
     */
    public function update(Request $request, Food $food): RedirectResponse
    {
        abort_unless($food->user_id === Auth::id(), 403);

        $fields = ['serving_size_g', 'calories_kcal', 'protein_g', 'carbs_g', 'fat_g'];
        $raw = [];
        foreach ($fields as $field) {
            if ($request->has($field) && $request->filled($field)) {
                $raw[$field] = \App\Support\Num::toDecimal($request->input($field));
            }
        }
        if (! empty($raw)) {
            $request->merge($raw);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'serving_name' => ['nullable', 'string', 'max:100'],
            'serving_size_g' => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
            'calories_kcal' => ['required', 'numeric', 'min:0', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'carbs_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'is_favorite' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $food->update([
            'name' => $validated['name'],
            'serving_name' => $validated['serving_name'] ?? '1 porsi',
            'serving_size_g' => $validated['serving_size_g'] ?? 100,
            'calories_kcal' => $validated['calories_kcal'],
            'protein_g' => $validated['protein_g'] ?? 0,
            'carbs_g' => $validated['carbs_g'] ?? 0,
            'fat_g' => $validated['fat_g'] ?? 0,
            'is_favorite' => (bool) ($request->has('is_favorite') && $request->input('is_favorite')),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('foods.index')->with('toast', [
            'type' => 'success',
            'message' => 'Makanan berhasil diperbarui.',
        ]);
    }

    /**
     * Delete food (cascade set null in food_logs).
     * DELETE /foods/{id}
     */
    public function destroy(Food $food): RedirectResponse
    {
        abort_unless($food->user_id === Auth::id(), 403);

        $food->delete();

        return redirect()->route('foods.index')->with('toast', [
            'type' => 'success',
            'message' => 'Makanan berhasil dihapus dari pustaka.',
        ]);
    }
}
