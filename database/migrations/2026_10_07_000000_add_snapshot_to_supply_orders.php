<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('supply_orders', 'emergency_from')) {
                $table->timestamp('emergency_from')->nullable()->after('generated_at');
            }
            if (! Schema::hasColumn('supply_orders', 'emergency_to')) {
                $table->timestamp('emergency_to')->nullable()->after('emergency_from');
            }
            if (! Schema::hasColumn('supply_orders', 'emergency_count')) {
                $table->unsignedInteger('emergency_count')->default(0)->after('emergency_to');
            }
        });

        if (! Schema::hasTable('supply_order_items')) {
            Schema::create('supply_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supply_order_id')->constrained('supply_orders')->cascadeOnDelete();
                $table->foreignId('supply_id')->nullable()->constrained('supplies')->nullOnDelete();
                $table->string('material');
                $table->unsignedDecimal('requested_quantity', 12, 2)->default(0);
                $table->unsignedDecimal('cancellation_quantity', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_order_items');

        foreach (['emergency_from', 'emergency_to', 'emergency_count'] as $column) {
            if (Schema::hasColumn('supply_orders', $column)) {
                Schema::table('supply_orders', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
