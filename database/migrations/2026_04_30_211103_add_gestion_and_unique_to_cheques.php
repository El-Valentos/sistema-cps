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
        // Paso 1: Agregar columna gestion
        Schema::table('cheques', function (Blueprint $table) {
            $table->integer('gestion')->nullable()->after('orden_pago_id');
        });

        // Paso 2: Llenar gestion para registros existentes (compatible con SQLite y MySQL)
        $cheques = DB::table('cheques')->get();
        foreach ($cheques as $cheque) {
            $fecha = $cheque->fecha_emision ?? $cheque->created_at;
            if ($fecha) {
                // Extraer año de la fecha (funciona en SQLite y MySQL)
                $year = date('Y', strtotime($fecha));
                DB::table('cheques')->where('id', $cheque->id)->update(['gestion' => $year]);
            }
        }

        // Paso 3: Agregar índice único para numero_cheque
        Schema::table('cheques', function (Blueprint $table) {
            $table->unique('numero_cheque', 'cheques_numero_cheque_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->dropUnique('cheques_numero_cheque_unique');
            $table->dropColumn('gestion');
        });
    }
};
