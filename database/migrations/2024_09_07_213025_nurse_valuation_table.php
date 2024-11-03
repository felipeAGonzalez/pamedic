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
        Schema::create('nurse_valuation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->string('nurse_valuation')->comment('Valoración de enfermería');
            $table->string('fase')->comment('Fase de la valoración');
            $table->string('nurse_intervention')->comment('Intervención de enfermería');
            $table->boolean('history')->default(false)->comment('Si el paciente tiene historial de caídas');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_valuation');

    }
};
