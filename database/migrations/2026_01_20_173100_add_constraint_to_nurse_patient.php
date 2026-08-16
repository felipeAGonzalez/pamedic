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
       if (! Schema::hasIndex('nurse_patient', 'nurse_patient_active_patient_id_index')) {
           Schema::table('nurse_patient', function (Blueprint $table) {
               $table->index('active_patient_id', 'nurse_patient_active_patient_id_index');
           });
       }

       Schema::table('nurse_patient', function (Blueprint $table) {
            $table->unique(['active_patient_id', 'date']);
        });
        Schema::table('schedules_patient', function (Blueprint $table) {
             $table->unique(['patient_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // La FK de active_patient_id necesita conservar un índice antes de
        // eliminar el índice único compuesto.
        if (! Schema::hasIndex('nurse_patient', 'nurse_patient_active_patient_id_index')) {
            Schema::table('nurse_patient', function (Blueprint $table) {
                $table->index('active_patient_id', 'nurse_patient_active_patient_id_index');
            });
        }

        Schema::table('nurse_patient', function (Blueprint $table) {
            $table->dropUnique(['active_patient_id', 'date']);
        });
    }
};
