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
        Schema::create('oxygen_therapy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->float('initial_oxygen_saturation')->comment('Saturación de oxígeno inicial del paciente');
            $table->float('final_oxygen_saturation')->comment('Saturación de oxígeno final del paciente');
            $table->time('start_time')->comment('Hora de inicio de la terapia de oxígeno');
            $table->time('end_time')->comment('Hora de finalización de la terapia de oxígeno');
            $table->float('oxygen_flow')->comment('Flujo de oxígeno administrado al paciente');
            $table->boolean('history')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oxygen_therapy');
    }
};
