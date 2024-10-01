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
        Schema::create('dialysis_monitoring', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->timestamp('date_hour')->comment('La fecha y hora del monitoreo de diálisis');
            $table->enum('vascular_access', ['fistula', 'catheter'])->comment('Acceso vascular');
            $table->enum('catheter_type', ['tunneling', 'no_tunneling'])->nullable()->comment('El tipo de catéter utilizado en la diálisis');
            $table->integer('needle_mesure')->nullable()->comment('La medida de la aguja utilizada en la diálisis');
            $table->enum('implantation', ['femoral', 'yugular', 'subclavia', 'brazo', 'antebrazo'])->comment('El tipo de implantación utilizada para la diálisis');
            $table->enum('side', ['right', 'left'])->comment('El lado del cuerpo en el que se realizó la diálisis');
            $table->date('collocation_date')->comment('La fecha en que se instaló el monitoreo de diálisis');
            $table->enum('serology', ['positivo', 'negativo'])->comment('El estado de serología del paciente');
            $table->date('serology_date')->comment('La fecha del ultimo análisis de serología');
            $table->string('blood_type')->comment('El tipo de sangre del paciente');
            $table->string('allergy')->comment('Cualquier alergia que pueda tener el paciente');
            $table->string('diagnostic')->comment('La información de diagnóstico relacionada con el monitoreo de diálisis');
            $table->boolean('history')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialysis_monitoring');
    }
};
