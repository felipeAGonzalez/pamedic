<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient', function (Blueprint $table) {
            $table->enum('insurance', ['IMSS', 'ISAPEG', 'NONE'])
                  ->default('NONE')
                  ->comment('Derechohabiencia del paciente')
                  ->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('patient', function (Blueprint $table) {
            $table->dropColumn('insurance');
        });
    }
};
