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
        Schema::table('dialysis_prescription', function (Blueprint $table) {
            $table->string('profile_ultrafilter')->nullable()->comment('Perfil del ultrafiltro')->change();
            $table->string('sodium_profile')->nullable()->comment('Perfil de sodio')->change();
        });

        Schema::table('pre_hemodialysis', function (Blueprint $table) {
            $table->text('observations')->nullable()->comment('Observaciones')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dialysis_prescription', function (Blueprint $table) {
            $table->string('profile_ultrafilter')->nullable()->comment('Perfil del ultrafiltro')->change();
            $table->string('sodium_profile')->nullable()->comment('Perfil de sodio')->change();
        });

        Schema::table('pre_hemodialysis', function (Blueprint $table) {
            $table->text('observations')->nullable()->comment('Observaciones')->change();

        });
    }
};
