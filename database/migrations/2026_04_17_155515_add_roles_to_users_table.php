<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Los roles se manejan con Spatie - no se agregan columnas al users
        // La tabla users ya tiene los campos necesarios
    }

    public function down(): void
    {
        //
    }
};
