<?php

namespace App\Services;

use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use App\Models\TransHemodialysis;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransHemodialysisScheduleService
{
    public function syncForPatient(int $patientId): Collection
    {
        return DB::transaction(function () use ($patientId) {
            $prescription = DialysisPrescription::where([
                'patient_id' => $patientId,
                'history' => 0,
            ])->latest('id')->first();
            $monitoring = DialysisMonitoring::where([
                'patient_id' => $patientId,
                'history' => 0,
            ])->latest('date_hour')->first();

            if (! $prescription || ! $monitoring || (int) $prescription->time <= 0) {
                return TransHemodialysis::where([
                    'patient_id' => $patientId,
                    'history' => 0,
                ])->orderBy('time')->orderBy('id')->get();
            }

            $duration = (int) $prescription->time;
            $offsets = [0];
            for ($minute = 15; $minute < $duration; $minute += 15) {
                $offsets[] = $minute;
            }
            if (end($offsets) !== $duration) {
                $offsets[] = $duration;
            }

            $records = TransHemodialysis::where([
                'patient_id' => $patientId,
                'history' => 0,
            ])->orderBy('time')->orderBy('id')->lockForUpdate()->get();

            $records->slice(count($offsets))->each->delete();
            $records = $records->take(count($offsets))->values();
            $startTime = Carbon::parse($monitoring->date_hour);
            $totalUltrafiltration = (float) $prescription->schedule_ultrafilter;

            foreach ($offsets as $index => $offset) {
                $values = [
                    'time' => $startTime->copy()->addMinutes($offset)->format('H:i:s'),
                    'ultrafiltration' => (int) round($totalUltrafiltration * $offset / $duration),
                ];

                $record = $records->get($index);
                if ($record) {
                    $record->update($values);
                    continue;
                }

                TransHemodialysis::create(array_merge($values, [
                    'patient_id' => $patientId,
                    'arterial_pressure' => 0,
                    'mean_pressure' => 0,
                    'heart_rate' => 0,
                    'respiratory_rate' => 18,
                    'temperature' => 36.5,
                    'arterial_pressure_monitor' => -200,
                    'venous_pressure_monitor' => 100,
                    'transmembrane_pressure_monitor' => 100,
                    'blood_flow' => 0,
                    'heparin' => 0,
                    'observations' => '',
                    'history' => 0,
                ]));
            }

            return TransHemodialysis::where([
                'patient_id' => $patientId,
                'history' => 0,
            ])->orderBy('time')->orderBy('id')->get();
        }, 3);
    }
}
