<?php

namespace App\Observers;
use App\Models\DialysisPrescription;
use App\Services\TransHemodialysisScheduleService;

class DialysisPrescriptionObserver
{
     /**
     * Handle the DialysisPrescription "updated" event.
     *
     * @param  \App\Models\DialysisPrescription  $dialysisPrescription
     * @return void
     */
    public function updated(DialysisPrescription $dialysisPrescription)
    {
        if ($dialysisPrescription->wasChanged(['time', 'schedule_ultrafilter'])) {
            app(TransHemodialysisScheduleService::class)
                ->syncForPatient($dialysisPrescription->patient_id);
        }
    }
}

