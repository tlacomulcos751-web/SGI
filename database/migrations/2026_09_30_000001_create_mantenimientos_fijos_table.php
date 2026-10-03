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
        if (!Schema::hasTable('mantenimientos_fijos')) {
            Schema::create('mantenimientos_fijos', function (Blueprint $table) {
                $table->id();
                $table->integer('producto_fijo_id')->index();
                $table->date('fecha');
                $table->string('tipo_servicio', 150);
                $table->integer('frecuencia_meses')->default(6)->nullable();
                $table->date('proxima_fecha')->nullable()->index();
                $table->string('tecnico', 255)->nullable();
                $table->decimal('costo', 10, 2)->nullable();
                $table->text('descripcion');
                $table->text('comentarios')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos_fijos');
    }
};
