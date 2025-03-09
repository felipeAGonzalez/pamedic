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
        Schema::table('medic_note', function (Blueprint $table) {
            $table->string('note_type')->nullable()->after('date');
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medic_note', function (Blueprint $table) {
            $table->dropColumn('note_type');
            $table->dropForeign('medic_note_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
};
