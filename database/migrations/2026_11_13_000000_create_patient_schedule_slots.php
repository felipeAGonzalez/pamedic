<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient');
            $table->unsignedTinyInteger('weekday');
            $table->foreignId('schedules_id')->constrained('schedules');
            $table->foreignId('machine_id')->constrained('machines');
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['patient_id', 'weekday'], 'patient_schedule_slots_patient_weekday_unique');
            $table->unique(['weekday', 'schedules_id', 'machine_id'], 'patient_schedule_slots_position_unique');
        });

        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->foreignId('patient_schedule_slot_id')
                ->nullable()
                ->after('clone_batch_id')
                ->constrained('patient_schedule_slots')
                ->nullOnDelete();
            $table->boolean('is_temporary')->default(false)->after('patient_schedule_slot_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_schedule_slot_id');
            $table->dropColumn('is_temporary');
        });

        Schema::dropIfExists('patient_schedule_slots');
    }
};
