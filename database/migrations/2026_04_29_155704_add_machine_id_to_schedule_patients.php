<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('schedules_patient', function (Blueprint $table) {
        if (!Schema::hasColumn('schedules_patient', 'machine_id')) {
            $table->foreignId('machine_id')
                ->nullable()
                ->constrained('machines')
                ->nullOnDelete();
        }
    });
}

public function down()
{
    Schema::table('schedules_patient', function (Blueprint $table) {
        $table->dropForeign(['machine_id']);
        $table->dropColumn('machine_id');
    });
}
};
