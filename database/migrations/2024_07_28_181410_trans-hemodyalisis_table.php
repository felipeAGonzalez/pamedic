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
        Schema::create('trans_hemodialysis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->time('time')->comment('Hora de la medición');
            $table->integer('arterial_pressure')->comment('Valor de la presión arterial');
            $table->integer('mean_pressure')->comment('Valor de la presión media');
            $table->integer('heart_rate')->comment('Valor de la frecuencia cardíaca');
            $table->integer('respiratory_rate')->comment('Valor de la frecuencia respiratoria');
            $table->decimal('temperature', 5, 2)->comment('Valor de la temperatura');
            $table->integer('arterial_pressure_monitor')->comment('Valor del monitor de presión arterial');
            $table->integer('venous_pressure_monitor')->comment('Valor del monitor de presión venosa');
            $table->integer('transmembrane_pressure_monitor')->comment('Valor del monitor de presión transmembrana');
            $table->integer('blood_flow')->comment('Valor del flujo sanguíneo');
            $table->integer('ultrafiltration')->comment('Valor de la ultrafiltración');
            $table->integer('heparin')->comment('Valor de la heparina');
            $table->text('observations')->nullable()->comment('Observaciones adicionales');
            $table->boolean('history')->default(false)->comment('Indicador si es un registro histórico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
