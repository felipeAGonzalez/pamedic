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
        Schema::create('post_hemodialysis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->string('final_ultrafiltration')->comment('ultrafiltración final');
            $table->string('treated_blood')->comment('sangre tratada');
            $table->string('ktv')->comment('ktv');
            $table->string('patient_temperature')->comment('temperatura del paciente');
            $table->string('blood_pressure_stand')->comment('presión arterial en posición de pie');
            $table->string('blood_pressure_sit')->comment('presión arterial sentado');
            $table->string('respiratory_rate')->comment('frecuencia respiratoria');
            $table->string('heart_rate')->comment('frecuencia cardíaca');
            $table->string('weight_out')->comment('peso de salida');
            $table->enum('fall_risk', ['high', 'medium', 'low'])->comment('Riesgo de caída');
            $table->boolean('history')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_hemodialysis');

    }
};
