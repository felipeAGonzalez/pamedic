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
        Schema::create('evaluation_risk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->time('hour')->comment('La hora de la evaluación');
            $table->enum('result',['0','2','4','6','8','10'])->comment('El resultado de la evaluación');
            $table->enum('fall_risk_trans',['high','medium','low'])->comment('El riesgo de caída durante el transporte');
            $table->boolean('history');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_risk');

    }
};
