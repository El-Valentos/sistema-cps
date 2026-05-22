<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_gasto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 20)->nullable();
            $table->string('descripcion')->nullable();
            $table->string('partida_presupuestaria', 50)->nullable();
            $table->decimal('presupuesto_anual', 12, 2)->nullable();
            $table->boolean('requiere_aprobacion')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_gasto');
    }
};
