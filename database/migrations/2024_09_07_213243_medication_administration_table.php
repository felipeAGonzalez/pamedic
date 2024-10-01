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
    Schema::create('medication_administration', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
        $table->foreign('patient_id')->references('id')->on('patient');
        $table->string('nurse_prepare')->comment('Nombre del enfermero/a encargado/a de preparar la medicación');
        $table->string('nurse_admin')->comment('Nombre del enfermero/a encargado/a de administrar la medicación');
        $table->string('medicine')->comment('Nombre del medicamento');
        $table->enum('route_administration',['oral','intravenous','intramuscular','subcutaneous','intradermal','inhalation'])->comment('Vía de administración del medicamento');
        $table->string('dilution')->comment('Dilución del medicamento');
        $table->string('velocity')->comment('Velocidad de administración del medicamento');
        $table->time('hour')->comment('Hora de administración del medicamento');
        $table->date('due_date')->comment('Fecha de vencimiento del medicamento');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_administration');

    }
};
