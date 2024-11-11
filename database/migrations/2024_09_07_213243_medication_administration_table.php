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
        $table->unsignedBigInteger('nurse_prepare_id')->comment('ID del enfermero/a encargado/a de preparar la medicación');
        $table->foreign('nurse_prepare_id')->references('id')->on('users');
        $table->unsignedBigInteger('nurse_admin_id')->comment('ID del enfermero/a encargado/a de administrar la medicación');
        $table->foreign('nurse_admin_id')->references('id')->on('users');
        $table->unsignedBigInteger('medicine_id')->comment('ID del medicamento a administrar');
        $table->foreign('medicine_id')->references('id')->on('medicines');
        $table->string('dilution')->comment('Dilución del medicamento');
        $table->string('velocity')->comment('Velocidad de administración del medicamento');
        $table->time('hour')->comment('Hora de administración del medicamento');
        $table->date('due_date')->comment('Fecha de vencimiento del medicamento');
        $table->boolean('history')->default(0)->comment('Estado de la administración del medicamento');
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
