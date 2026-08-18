<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_orders', function (Blueprint $table) {
            $table->id();
            $table->date('period_start')->comment('Inicio del periodo del pedido');
            $table->date('period_end')->comment('Fin del periodo del pedido');
            $table->timestamp('generated_at')->comment('Cuando se generó el PDF del pedido');
            $table->index(['period_start', 'period_end']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_orders');
    }
};
