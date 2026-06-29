<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('material');
            $table->enum('type', array_keys(\App\Models\Supply::TYPES));
            $table->enum('for_vascular_access', ['catheter', 'fistula', 'both', 'no_apply']);
            $table->unsignedInteger('existencias')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
