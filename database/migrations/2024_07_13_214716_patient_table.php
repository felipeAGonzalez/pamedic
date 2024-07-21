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
        Schema::create('patient', function (Blueprint $table) {
            $table->id();
            $table->string('expedient_number')->comment('Número de expediente del paciente');
            $table->string('name')->comment('Nombre del paciente');
            $table->string('last_name')->comment('Apellido paterno del paciente');
            $table->string('last_name_two')->comment('Apellido materno del paciente');
            $table->date('birth_date')->comment('Fecha de nacimiento del paciente');
            $table->date('date_entry')->comment('Fecha de ingreso');
            $table->string('contact_phone_number')->nullable();
            $table->enum('gender', ['M', 'F'])->comment('Género del paciente');
            $table->string('photo')->nullable()->comment('Foto del paciente');
            $table->unique(['expedient_number','name', 'last_name'], 'patient_unique')->comment = 'Indice (unique) de los campos expedient_number, name y last_name, para que no existan tuplas duplicadas.';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient');
    }
};
