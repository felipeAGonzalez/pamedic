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
        Schema::table('pre_hemodialysis', function (Blueprint $table) {
            $table->string('dextrostix')->nullable()->comment('Destroxtis')->change();
            $table->enum('itchiness', ['high', 'medium', 'low', 'N/A', 'N/P'])->comment('Prurito (high, medium, low, N/A, N/P)')->change();
            $table->enum('pallor_skin', ['high', 'medium', 'low', 'N/A', 'N/P'])->comment('Palidez de la piel (high, medium, low, N/A, N/P)')->change();
            $table->enum('edema', ['high', 'medium', 'low', 'N/A', 'N/P'])->comment('Edema (high, medium, low, N/A, N/P)')->change();
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
