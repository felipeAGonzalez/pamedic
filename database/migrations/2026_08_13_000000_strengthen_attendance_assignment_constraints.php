<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->assertNoDuplicates('active_patient', ['patient_id', 'date']);
        $this->assertNoDuplicates('nurse_patient', ['active_patient_id']);
        $this->assertNoDuplicates('schedules_patient', ['patient_id', 'date'], 'deleted_at IS NULL');

        Schema::table('active_patient', function (Blueprint $table) {
            $table->unique(['patient_id', 'date'], 'active_patient_patient_date_unique');
        });

        Schema::table('nurse_patient', function (Blueprint $table) {
            $table->dropUnique(['active_patient_id', 'date']);
            $table->unique('active_patient_id', 'nurse_patient_active_patient_unique');
        });

        // MySQL unique indexes allow multiple NULL values. The generated column is
        // the patient id only while the soft-deleted schedule remains current.
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->dropUnique(['patient_id', 'date']);
        });
        DB::statement('ALTER TABLE schedules_patient ADD COLUMN current_patient_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN patient_id ELSE NULL END) STORED');
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->unique(['current_patient_id', 'date'], 'schedules_patient_current_patient_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->dropUnique('schedules_patient_current_patient_date_unique');
        });
        DB::statement('ALTER TABLE schedules_patient DROP COLUMN current_patient_id');
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->unique(['patient_id', 'date']);
        });

        Schema::table('nurse_patient', function (Blueprint $table) {
            $table->dropUnique('nurse_patient_active_patient_unique');
            $table->unique(['active_patient_id', 'date']);
        });
        Schema::table('active_patient', function (Blueprint $table) {
            $table->dropUnique('active_patient_patient_date_unique');
        });
    }

    private function assertNoDuplicates(string $table, array $columns, ?string $where = null): void
    {
        $query = DB::table($table)->select($columns)->selectRaw('COUNT(*) AS duplicate_count')->groupBy($columns)->havingRaw('COUNT(*) > 1');
        if ($where) {
            $query->whereRaw($where);
        }
        if ($query->exists()) {
            throw new \RuntimeException("No se pueden crear las restricciones de {$table}: existen registros duplicados. Resuélvalos manualmente y vuelva a ejecutar la migración.");
        }
    }
};
