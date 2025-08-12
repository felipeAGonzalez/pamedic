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
        Schema::create('time_out', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable()->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->boolean('patient_identification')->default(false)->comment('Identificación del paciente verificada');
            $table->boolean('scheduled_procedure')->default(false)->comment('Procedimiento programado verificado');
            $table->boolean('dialysis_prescription')->default(false)->comment('Prescripción de diálisis verificada');
            $table->boolean('dialyzer_check')->default(false)->comment('Verificación del dializador');
            $table->boolean('bleeding_check')->default(false)->comment('Revisión de sangrado realizada');
            $table->boolean('vascular_access_check')->default(false)->comment('Verificación del acceso vascular');
            $table->boolean('history')->default(false)->comment('Bandera de registro historico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_out');
    }
};
