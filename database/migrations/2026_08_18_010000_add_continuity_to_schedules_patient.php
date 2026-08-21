<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->boolean('continue_schedule')->default(true)->after('deleted_at');
            $table->index(['patient_id', 'continue_schedule'], 'schedules_patient_continuity_index');
        });
    }

    public function down(): void
    {
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->dropIndex('schedules_patient_continuity_index');
            $table->dropColumn('continue_schedule');
        });
    }
};
