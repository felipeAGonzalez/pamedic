<?php

namespace App\Observers;
use App\Models\DialysisMonitoring;
use App\Models\TransHemodialysis;

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
       if(array_key_exists('date_hour', $dialysisMonitoring->getDirty() ))
         {
            $transHemodialysis = TransHemodialysis::where(['patient_id' => $dialysisMonitoring->patient_id,'history' => 0])->get();
            $hour = date('H:i', strtotime($dialysisMonitoring->date_hour));
            foreach ($transHemodialysis as $index => $hemodialysis) {
                $hemodialysis->update([
                   'time' => date('H:i', strtotime($hour) + ($index * 15 * 60)),
                ]);
            }
         }
    }
}

