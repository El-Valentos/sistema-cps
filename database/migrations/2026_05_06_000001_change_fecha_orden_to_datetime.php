<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->timestamp('fecha_orden')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->date('fecha_orden')->nullable()->change();
        });
    }
};
