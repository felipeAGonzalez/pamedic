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
        Schema::create('verification', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable()->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->boolean('patient_name')->default(false)->comment('Nombre del paciente verificado');
            $table->boolean('date_of_birth')->default(false)->comment('Fecha de nacimiento verificada');
            $table->boolean('scheduled_procedure')->default(false)->comment('Procedimiento programado verificado');
            $table->boolean('patient_id_badge')->default(false)->comment('Gafete del paciente colocado');
            $table->boolean('nurse_sheet_identified')->default(false)->comment('Hoja de enfermería identificada');
            $table->boolean('hep_b_c_serology_m_4_months')->default(false)->comment('Serología Hepatitis B y C menor a 4 meses verificada');
            $table->boolean('serology_m_6_months')->default(false)->comment('Serología menor a 6 meses verificada');
            $table->boolean('hd_machine_test_passed')->default(false)->comment('Máquina de hemodiálisis pasó prueba');
            $table->boolean('kit_per_vascular_access')->default(false)->comment('Kit preparado de acuerdo al acceso vascular');
            $table->boolean('allergies')->default(false)->comment('Alergias identificadas');
            $table->boolean('dialyzer_per_prescription')->default(false)->comment('Dializador de acuerdo a la prescripción');
            $table->boolean('reprocessed_dialyzer_label')->default(false)->comment('Etiqueta del dializador reprocesado verificada');
            $table->boolean('vascular_access')->default(false)->comment('Acceso vascular verificado');
            $table->boolean('history')->default(false)->comment('Bandera de registro historico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verfication');
    }
};
