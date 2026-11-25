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
        if (Schema::hasTable('productos_fijos') && !Schema::hasColumn('productos_fijos', 'proyecto')) {
            Schema::table('productos_fijos', function (Blueprint $table) {
                $table->string('proyecto', 255)->nullable()->after('responsable');
            });
        }

        if (Schema::hasTable('vehiculos') && !Schema::hasColumn('vehiculos', 'proyecto')) {
            Schema::table('vehiculos', function (Blueprint $table) {
                $table->string('proyecto', 255)->nullable()->after('responsable');
            });
        }

        if (Schema::hasTable('movimiento') && !Schema::hasColumn('movimiento', 'proyecto')) {
            Schema::table('movimiento', function (Blueprint $table) {
                $table->string('proyecto', 255)->nullable()->after('documentacion');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('productos_fijos') && Schema::hasColumn('productos_fijos', 'proyecto')) {
            Schema::table('productos_fijos', function (Blueprint $table) {
                $table->dropColumn('proyecto');
            });
        }

        if (Schema::hasTable('vehiculos') && Schema::hasColumn('vehiculos', 'proyecto')) {
            Schema::table('vehiculos', function (Blueprint $table) {
                $table->dropColumn('proyecto');
            });
        }

        if (Schema::hasTable('movimiento') && Schema::hasColumn('movimiento', 'proyecto')) {
            Schema::table('movimiento', function (Blueprint $table) {
                $table->dropColumn('proyecto');
            });
        }
    }
};
