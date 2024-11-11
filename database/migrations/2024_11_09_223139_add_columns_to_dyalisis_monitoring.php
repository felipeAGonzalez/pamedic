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
        Schema::table('dialysis_monitoring', function (Blueprint $table) {
            $table->string('machine_number')->after('date_hour')->comment('Número de la máquina');
            $table->string('session_number')->after('machine_number')->comment('Número de la sesión');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dialysis_monitoring', function (Blueprint $table) {
            $table->dropColumn('machine_number');
            $table->dropColumn('session_number');
        });
    }
};
