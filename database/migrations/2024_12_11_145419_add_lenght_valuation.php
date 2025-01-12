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
        Schema::table('nurse_valuation', function (Blueprint $table) {
            $table->string('nurse_valuation', 800)->comment('Valoración de enfermería')->change();
            $table->string('nurse_intervention',800)->comment('Intervención de enfermería')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
