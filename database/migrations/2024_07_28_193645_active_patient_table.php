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
        Schema::create('active_patient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->date('date')->comment('Fecha de sesión');
            $table->boolean('active')->default(true)->comment('Indicador si el paciente está activo');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_patient');
    }
};
