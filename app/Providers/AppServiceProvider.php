<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB; // <-- Se agregó para poder hablar con MySQL directo

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Forzar el idioma de MySQL y apagar el modo estricto
        config([
            'database.connections.mysql.charset' => 'utf8mb4',
            'database.connections.mysql.collation' => 'utf8mb4_general_ci',
            'database.connections.mysql.strict' => false,
        ]);

        // 2. LA MAGIA NUEVA: Quitarle el miedo a NEUBOX con las vistas pesadas
        try {
            DB::statement('SET SQL_BIG_SELECTS=1');
        } catch (\Exception $e) {
            // Se ignora en comandos de consola por si la BD no está lista
        }

        // 3. Mantener HTTPS solo en producción sin forzar un solo dominio
        if (config('app.env') === 'production') {
            // URL::forceRootUrl(config('app.url')); // Se comenta para permitir múltiples dominios
            URL::forceScheme('https');
        }

        // 4. View Composer para Alertas de Stock Crítico
        \Illuminate\Support\Facades\View::composer('layouts.navigation', function ($view) {
            try {
                $alertasStockCritico = DB::table('vista_productos_detalle')
                    ->where('existencia', '<=', 5)
                    ->whereIn('estado', ['activo', 'disponible'])
                    ->orderByRaw("FIELD(tipo, 'consumible', 'fijo', 'compraventa')")
                    ->orderBy('existencia', 'asc')
                    ->get();
            } catch (\Exception $e) {
                $alertasStockCritico = collect();
            }
            $view->with('alertasStockCritico', $alertasStockCritico);
        });
    }
}