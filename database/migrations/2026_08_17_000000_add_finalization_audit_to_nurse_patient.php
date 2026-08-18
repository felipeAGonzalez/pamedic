<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nurse_patient', function (Blueprint $table) {
            $table->unsignedBigInteger('finalized_by')->nullable()->after('history');
            $table->timestamp('finalized_at')->nullable()->after('finalized_by');
            $table->date('exceptional_start_date')->nullable()->after('finalized_at');
        });
    }

    public function down(): void
    {
        Schema::table('nurse_patient', function (Blueprint $table) {
            $table->dropColumn([
                'finalized_by',
                'finalized_at',
                'exceptional_start_date',
            ]);
        });
    }
};
