<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules_patient', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->comment('El ID del paciente asociado con el horario');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->unsignedBigInteger('schedules_id')->nullable()->comment('El ID del horario asociado el paciente');
            $table->foreign('schedules_id')->references('id')->on('schedules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::dropIfExists('schedules_patient');
    }
};
