<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->unsignedDecimal('units_per_patient', 10, 2)->default(1)->after('for_vascular_access');
            $table->unsignedDecimal('existencias', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->unsignedInteger('existencias')->default(0)->change();
            $table->dropColumn('units_per_patient');
        });
    }
};
