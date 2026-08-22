<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_clone_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('source_year');
            $table->unsignedTinyInteger('source_week');
            $table->unsignedSmallInteger('target_year');
            $table->unsignedTinyInteger('target_week');
            $table->char('snapshot_hash', 64)->nullable();
            $table->unsignedInteger('records_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('cloned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('undone_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();

            $table->index(['source_year', 'source_week', 'status'], 'schedule_clone_batches_source_index');
        });

        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->foreignId('clone_batch_id')
                ->nullable()
                ->after('continue_schedule')
                ->constrained('schedule_clone_batches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedules_patient', function (Blueprint $table) {
            $table->dropConstrainedForeignId('clone_batch_id');
        });

        Schema::dropIfExists('schedule_clone_batches');
    }
};
