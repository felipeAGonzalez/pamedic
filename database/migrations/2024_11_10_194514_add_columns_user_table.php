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
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 50)->change();
            $table->string('last_name_one', 50)->after('name')->comment('Primer apellido');
            $table->string('last_name_two', 50)->after('last_name_one')->comment('Segundo apellido');
            $table->string('profesional_id', 15)->after('last_name_two')->comment('Cédula profesional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->dropColumn('last_name_one');
            $table->dropColumn('last_name_two');
            $table->dropColumn('profesional_id');
        });
    }
};
