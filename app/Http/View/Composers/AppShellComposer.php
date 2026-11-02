<?php

namespace App\Http\View\Composers;

use App\Models\BodyMeasurement;
use App\Models\DailyActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppShellComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user) {
            $view->with([
                'shellToday' => Carbon::now('Asia/Jakarta')->toDateString(),
                'shellLatestWeight' => null,
                'shellTodaySteps' => null,
            ]);
            return;
        }

        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';
        $shellToday = Carbon::now($tz)->toDateString();

        $shellLatestWeight = BodyMeasurement::where('user_id', $user->id)
            ->whereNotNull('weight_kg')
            ->orderByDesc('measured_at')
            ->first()?->weight_kg;

        $shellTodaySteps = DailyActivity::where('user_id', $user->id)
            ->where('activity_date', $shellToday)
            ->first()?->steps;

        $view->with([
            'shellToday' => $shellToday,
            'shellLatestWeight' => $shellLatestWeight,
            'shellTodaySteps' => $shellTodaySteps,
        ]);
    }
}
