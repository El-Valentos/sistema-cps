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
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->string('liquidador_texto')->nullable()->after('liquidador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->dropColumn('liquidador_texto');
        });
    }
};
