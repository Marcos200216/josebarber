<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // PIN de 4 dígitos guardado hasheado. Nullable para no romper citas de prueba anteriores.
            $table->string('pin_hash')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('pin_hash');
        });
    }
};