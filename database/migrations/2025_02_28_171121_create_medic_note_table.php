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
        Schema::create('medic_note', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con la nota medica');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->date('date')->comment('Fecha de la nota medica');
            $table->string('patient', 500)->comment('Nombre del paciente');
            $table->string('subjective', 500)->comment('Descripción subjetiva del estado del paciente');
            $table->string('objective', 500)->comment('Descripción objetiva del estado del paciente');
            $table->string('prognosis', 500)->comment('Pronóstico del paciente');
            $table->string('plan', 500)->comment('Plan de tratamiento para el paciente');
            $table->boolean('history')->default(0)->comment('Indica si la nota medica es histórica');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medic_note');
    }
};
