<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Support\Num;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BodyMeasurementController extends Controller
{
    /**
     * Upsert body measurement for the given date (D14).
     */
    public function upsert(Request $request, string $date): RedirectResponse
    {
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $e) {
            return back()->withErrors(['date' => 'Format tanggal tidak valid.']);
        }

        $user = Auth::user();

        $data = [];
        if ($request->has('weight_kg')) {
            $rawWeight = $request->input('weight_kg');
            $weight = Num::toDecimal($rawWeight);
            if ($rawWeight !== null && $rawWeight !== '' && ($weight === null || $weight < 20 || $weight > 300)) {
                return back()->withErrors(['weight_kg' => 'Berat badan harus di antara 20 dan 300 kg.']);
            }
            if ($weight !== null) {
                $data['weight_kg'] = $weight;
            }
        }

        if ($request->has('waist_cm')) {
            $data['waist_cm'] = Num::toDecimal($request->input('waist_cm'));
        }
        if ($request->has('chest_cm')) {
            $data['chest_cm'] = Num::toDecimal($request->input('chest_cm'));
        }
        if ($request->has('arm_cm')) {
            $data['arm_cm'] = Num::toDecimal($request->input('arm_cm'));
        }
        if ($request->has('thigh_cm')) {
            $data['thigh_cm'] = Num::toDecimal($request->input('thigh_cm'));
        }
        if ($request->has('notes')) {
            $data['notes'] = $request->input('notes');
        }

        if (empty($data)) {
            return back()->withErrors(['weight_kg' => 'Isi minimal satu metrik pengukuran.']);
        }

        $measurement = BodyMeasurement::where('user_id', $user->id)
            ->whereDate('measured_at', $parsedDate)
            ->first();

        if ($measurement) {
            $measurement->update($data);
        } else {
            $data['user_id'] = $user->id;
            $data['measured_at'] = $parsedDate->toDateString();
            BodyMeasurement::create($data);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Catatan berat badan berhasil disimpan.',
        ]);
    }
}
