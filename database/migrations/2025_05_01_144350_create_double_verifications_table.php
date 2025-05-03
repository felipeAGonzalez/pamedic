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
        Schema::create('double_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable()->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->boolean('correct_medication')->default(0)->comment('Campo para verificar si la medicación es correcta');
            $table->boolean('correct_dosage')->default(0)->comment('Campo para verificar si la dosis es correcta');
            $table->boolean('correct_dilution')->default(0)->comment('Campo para verificar si la dilución es correcta');
            $table->boolean('correct_time')->default(0)->comment('Campo para verificar si la hora es correcta');
            $table->boolean('expiration_verification')->default(0)->comment('Campo para verificar la expiración');
            $table->boolean('medication_record')->default(0)->comment('Campo para registrar la medicación');
            $table->boolean('patient_education')->default(0)->comment('Campo para registrar la educación al paciente');
            $table->boolean('medication_identification')->default(0)->comment('Campo para identificar la medicación');
            $table->unsignedBigInteger('nurse_id')->nullable()->comment('ID del enfermero/a que realizó la verificación de la medicación');
            $table->foreign('nurse_id')->references('id')->on('users');
            $table->boolean('history')->default(false)->comment('si paso a ser historico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('double_verifications');
    }
};
