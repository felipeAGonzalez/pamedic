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

        // Mantiene un índice independiente para la FK de patient_id. MySQL
        // puede usar el índice único compuesto como soporte de la FK y luego
        // impedir que se elimine durante el rollback.
        if (! Schema::hasIndex('active_patient', 'active_patient_patient_id_index')) {
            Schema::table('active_patient', function (Blueprint $table) {
                $table->index('patient_id', 'active_patient_patient_id_index');
            });
        }

        if (! Schema::hasIndex('active_patient', 'active_patient_patient_date_unique')) {
            Schema::table('active_patient', function (Blueprint $table) {
                $table->unique(['patient_id', 'date'], 'active_patient_patient_date_unique');
            });
        }

        // La FK de active_patient_id necesita un índice cuyo primer campo sea
        // active_patient_id. Se crea el reemplazo antes de eliminar el compuesto.
        if (! Schema::hasIndex('nurse_patient', 'nurse_patient_active_patient_unique')) {
            Schema::table('nurse_patient', function (Blueprint $table) {
                $table->unique('active_patient_id', 'nurse_patient_active_patient_unique');
            });
        }

        if (Schema::hasIndex('nurse_patient', 'nurse_patient_active_patient_id_date_unique')) {
            Schema::table('nurse_patient', function (Blueprint $table) {
                $table->dropUnique('nurse_patient_active_patient_id_date_unique');
            });
        }

        // MySQL unique indexes allow multiple NULL values. The generated column is
        // the patient id only while the soft-deleted schedule remains current.
        // La FK de patient_id también necesita conservar un índice propio.
        if (! Schema::hasIndex('schedules_patient', 'schedules_patient_patient_id_index')) {
            Schema::table('schedules_patient', function (Blueprint $table) {
                $table->index('patient_id', 'schedules_patient_patient_id_index');
            });
        }

        if (Schema::hasIndex('schedules_patient', 'schedules_patient_patient_id_date_unique')) {
            Schema::table('schedules_patient', function (Blueprint $table) {
                $table->dropUnique('schedules_patient_patient_id_date_unique');
            });
        }

        if (! Schema::hasColumn('schedules_patient', 'current_patient_id')) {
            DB::statement('ALTER TABLE schedules_patient ADD COLUMN current_patient_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN patient_id ELSE NULL END) STORED');
        }

        if (! Schema::hasIndex('schedules_patient', 'schedules_patient_current_patient_date_unique')) {
            Schema::table('schedules_patient', function (Blueprint $table) {
                $table->unique(['current_patient_id', 'date'], 'schedules_patient_current_patient_date_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('schedules_patient', 'schedules_patient_patient_id_date_unique')) {
            Schema::table('schedules_patient', function (Blueprint $table) {
                $table->unique(['patient_id', 'date'], 'schedules_patient_patient_id_date_unique');
            });
        }

        if (Schema::hasIndex('schedules_patient', 'schedules_patient_current_patient_date_unique')) {
            Schema::table('schedules_patient', function (Blueprint $table) {
                $table->dropUnique('schedules_patient_current_patient_date_unique');
            });
        }

        if (Schema::hasColumn('schedules_patient', 'current_patient_id')) {
            DB::statement('ALTER TABLE schedules_patient DROP COLUMN current_patient_id');
        }

        if (Schema::hasIndex('schedules_patient', 'schedules_patient_patient_id_index')) {
            Schema::table('schedules_patient', function (Blueprint $table) {
                $table->dropIndex('schedules_patient_patient_id_index');
            });
        }

        if (! Schema::hasIndex('nurse_patient', 'nurse_patient_active_patient_id_date_unique')) {
            Schema::table('nurse_patient', function (Blueprint $table) {
                $table->unique(['active_patient_id', 'date'], 'nurse_patient_active_patient_id_date_unique');
            });
        }

        if (Schema::hasIndex('nurse_patient', 'nurse_patient_active_patient_unique')) {
            Schema::table('nurse_patient', function (Blueprint $table) {
                $table->dropUnique('nurse_patient_active_patient_unique');
            });
        }

        // La FK debe contar con un índice alternativo antes de retirar el
        // índice único compuesto.
        if (! Schema::hasIndex('active_patient', 'active_patient_patient_id_index')) {
            Schema::table('active_patient', function (Blueprint $table) {
                $table->index('patient_id', 'active_patient_patient_id_index');
            });
        }

        if (Schema::hasIndex('active_patient', 'active_patient_patient_date_unique')) {
            Schema::table('active_patient', function (Blueprint $table) {
                $table->dropUnique('active_patient_patient_date_unique');
            });
        }
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
