<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_pago', function (Blueprint $table) {
            $table->id();
            $table->string('gestion', 4)->default(date('Y'));
            $table->string('ciudad', 100)->default('Cochabamba');
            $table->string('numero_orden', 20)->nullable()->unique();
            $table->foreignId('beneficiario_id')->constrained('beneficiarios');
            $table->string('a_la_orden_de')->nullable();
            $table->decimal('monto_total', 14, 2)->default(0);
            $table->decimal('retencion_7', 14, 2)->default(0);
            $table->decimal('retencion_35', 14, 2)->default(0);
            $table->decimal('devolucion_retencion', 14, 2)->default(0);
            $table->decimal('neto_pagar', 14, 2)->default(0);
            $table->text('concepto');
            $table->string('concepto_pago')->nullable();
            $table->foreignId('categoria_gasto_id')->nullable()->constrained('categorias_gasto')->nullOnDelete();
            $table->string('tipo_orden', 50)->nullable();
            $table->integer('numero_fojas')->default(0);
            $table->boolean('tiene_respaldo')->default(false);
            $table->foreignId('creado_por')->constrained('users');
            $table->foreignId('liquidador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_orden')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('estado', 50)->default('pendiente_tesoreria');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_pago');
    }
};
