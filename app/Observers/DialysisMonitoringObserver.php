<?php

namespace App\Observers;
use App\Models\DialysisMonitoring;
use App\Services\TransHemodialysisScheduleService;

class DialysisMonitoringObserver
{
     /**
     * Handle the DialysisMonitoring "updated" event.
     *
     * @param  \App\Models\DialysisMonitoring  $dialysisMonitoring
     * @return void
     */
    public function updated(DialysisMonitoring $dialysisMonitoring)
    {
        if ($dialysisMonitoring->wasChanged('date_hour')) {
            app(TransHemodialysisScheduleService::class)
                ->syncForPatient($dialysisMonitoring->patient_id);
        }
    }
}

