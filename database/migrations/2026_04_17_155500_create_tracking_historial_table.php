<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_pago_id')->constrained('ordenes_pago')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('area_origen_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('area_destino_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('accion', 50);
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50);
            $table->text('comentario')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_historial');
    }
};
