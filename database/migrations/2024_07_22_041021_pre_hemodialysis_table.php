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
        Schema::create('pre_hemodialysis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('El ID del paciente asociado con el monitoreo de diálisis');
            $table->foreign('patient_id')->references('id')->on('patient');
            $table->decimal('previous_initial_weight')->comment('Peso inicial anterior');
            $table->decimal('previous_final_weight')->comment('Peso final anterior');
            $table->decimal('previous_weight_gain')->comment('Ganancia de peso anterior');
            $table->decimal('initial_weight')->comment('Peso inicial');
            $table->decimal('dry_weight')->comment('Peso seco');
            $table->decimal('weight_gain')->comment('Ganancia de peso');
            $table->integer('reuse_number')->comment('Número de reutilización');
            $table->integer('sitting_blood_pressure')->comment('Presión arterial sentado');
            $table->integer('standing_blood_pressure')->comment('Presión arterial de pie');
            $table->decimal('body_temperature')->comment('Temperatura corporal');
            $table->integer('heart_rate')->comment('Ritmo cardíaco');
            $table->integer('respiratory_rate')->comment('Ritmo respiratorio');
            $table->integer('oxygen_saturation')->comment('Saturación de oxígeno');
            $table->integer('conductivity')->comment('Conductividad');
            $table->integer('destrostix')->comment('Destrostix');
            $table->integer('itchiness')->comment('Picazón');
            $table->enum('pallor_skin', ['high', 'medium', 'low'])->comment('Palidez de la piel');
            $table->enum('edema', ['high', 'medium', 'low'])->comment('Edema');
            $table->string('vascular_access_conditions')->comment('Condiciones de acceso vascular');
            $table->enum('fall_risk', ['high', 'medium', 'low'])->comment('Riesgo de caída');
            $table->text('observations')->comment('Observaciones');
            $table->boolean('history')->default(false);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_hemodialysis');
    }
};


