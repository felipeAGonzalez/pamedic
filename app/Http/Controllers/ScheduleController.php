<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Patient;
use App\Models\SchedulePatients;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{

    public function index()
    {
        return view('schedule.index');
    }

}
