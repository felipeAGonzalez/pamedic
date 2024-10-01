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
        Schema::create('dialysis_prescription', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->enum('type_dialyzer', ['HF80S', 'F6ELISIO21H', 'F6ELISIO19H'])->comment('Tipo de dializador');
            $table->string('time')->comment('Hora');
            $table->string('blood_flux')->comment('Flujo de sangre');
            $table->string('flux_dialyzer')->comment('Flujo del dializador');
            $table->string('heparin')->comment('Heparina');
            $table->string('schedule_ultrafilter')->comment('Programación del ultrafiltro');
            $table->string('profile_ultrafilter')->comment('Perfil del ultrafiltro');
            $table->string('sodium_profile')->comment('Perfil de sodio');
            $table->string('machine_temperature')->comment('Temperatura de la máquina');
            $table->boolean('history')->default(false)->comment('Historial');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialysis_prescription');
    }
};
