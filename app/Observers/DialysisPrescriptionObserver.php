<?php

namespace App\Observers;
use App\Models\DialysisPrescription;
use App\Models\TransHemodialysis;

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
       if(array_key_exists('schedule_ultrafilter', $dialysisPrescription->getDirty() ))
         {
            $times = ($dialysisPrescription->time / 15 ) + 1;
            $scheduleUltrafilter = $dialysisPrescription->schedule_ultrafilter / ($times -1);
            $transHemodialysis = TransHemodialysis::where(['patient_id' => $dialysisPrescription->patient_id,'history' => 0])->get();
            foreach ($transHemodialysis as $index => $hemodialysis) {
                $hemodialysis->update([
                    'ultrafiltration' => $index * $scheduleUltrafilter,
                ]);
            }
         }
    }
}

