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
       Schema::table('citas', function (Blueprint $table) {
           $table->string('cliente_email', 120)->nullable()->after('cliente_telefono');
       });
   }

   public function down(): void
   {
       Schema::table('citas', function (Blueprint $table) {
           $table->dropColumn('cliente_email');
       });
   }
};
