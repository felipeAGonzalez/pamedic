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
    // Schema::table('schedules_patient', function (Blueprint $table) {
    //     $table->unsignedBigInteger('machine_id')->after('date')->comment('El ID de la maquina asociado con el paciente y horario');
    //     $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
    // });
}

public function down()
{
    // Schema::table('schedules_patient', function (Blueprint $table) {
    //     $table->dropForeign(['machine_id']);
    //     $table->dropColumn('machine_id');
    // });
}
};
