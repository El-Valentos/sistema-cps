<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_pago_id')->constrained('ordenes_pago')->cascadeOnDelete();
            $table->string('numero_cheque', 20);
            $table->string('numero_cuenta', 50)->nullable();
            $table->string('banco', 100);
            $table->date('fecha_emision');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto', 14, 2);
            $table->string('monto_literal')->nullable();
            $table->foreignId('emitido_por')->constrained('users');
            $table->timestamp('fecha_emision_sistema')->nullable();
            $table->string('estado', 50)->default('emitido');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
