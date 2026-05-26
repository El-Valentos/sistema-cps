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
            $table->string('beneficiario_nombre')->nullable()->after('beneficiario_id');
            $table->string('beneficiario_apellidos')->nullable()->after('beneficiario_nombre');
            $table->string('beneficiario_ci_nit', 50)->nullable()->after('beneficiario_apellidos');
            $table->string('beneficiario_direccion')->nullable()->after('beneficiario_ci_nit');
            $table->string('beneficiario_telefono', 30)->nullable()->after('beneficiario_direccion');
        });

        // Backfill: copiar datos de la relación para registros existentes
        // Usar enfoque compatible con SQLite y MySQL
        $ordenes = DB::table('ordenes_pago')->whereNotNull('beneficiario_id')->get();
        foreach ($ordenes as $orden) {
            $beneficiario = DB::table('beneficiarios')->where('id', $orden->beneficiario_id)->first();
            if ($beneficiario) {
                DB::table('ordenes_pago')->where('id', $orden->id)->update([
                    'beneficiario_nombre' => $beneficiario->nombre_razon_social,
                    'beneficiario_apellidos' => $beneficiario->apellidos,
                    'beneficiario_ci_nit' => $beneficiario->ci_nit,
                    'beneficiario_direccion' => $beneficiario->direccion,
                    'beneficiario_telefono' => $beneficiario->telefono,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->dropColumn([
                'beneficiario_nombre',
                'beneficiario_apellidos',
                'beneficiario_ci_nit',
                'beneficiario_direccion',
                'beneficiario_telefono'
            ]);
        });
    }
};
