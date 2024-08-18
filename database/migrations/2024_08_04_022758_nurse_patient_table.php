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
        Schema::create('nurse_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('active_patient_id')->references('id')->on('active_patient');
            $table->unsignedBigInteger('user_id')->comment('El ID del enfermero asociado para el monitoreo de diálisis');
            $table->foreign('user_id')->references('id')->on('users');
            $table->date('date')->comment('Fecha de sesión');
            $table->boolean('history')->default(false)->comment('Indicador si el paciente está activo');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_patient');
    }
};
