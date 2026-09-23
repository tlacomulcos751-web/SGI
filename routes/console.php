<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sgi:estandarizar-etiquetas', function () {
    $this->info('Iniciando estandarización de etiquetas SGI...');

    $categoriasEstandar = [
        'cómputo' => ['computo', 'cómputo', 'fuente de poder'],
        'redes e infraestructura ti' => ['equipos de red', 'servidores y almacenamiento', 'electronica', 'infraestructura ti'],
        'seguridad y videovigilancia' => ['seguridad', 'categoria cctv', 'control de acceso', 'seguridad y vigilancia', 'seguridad y videovigilancia'],
        'energía y respaldo' => ['energía', 'energia', 'energía y respaldo'],
        'telefonía y comunicación' => ['telefonía', 'telefonia', 'telefonía y comunicación'],
        'audio, video y tv' => ['audio y video', 'tv y video', 'audio, video y tv'],
        'mobiliario y oficina' => ['muebles', 'mobiliario', 'mobiliario y oficina'],
        'protección contra incendios' => ['equipo contra incendio', 'protección contra incendios'],
        'uso doméstico y cafetería' => ['domestico', 'uso doméstico', 'uso doméstico y cafetería'],
        'herramientas y mantenimiento' => ['equipo de reparacion', 'herramientas y mantenimiento'],
        'cableado' => ['cableado', 'cables', 'cable'],
        'equipo nuevo' => ['equipo nuevo'],
        'equipo usado' => ['equipo usado', 'EQUIPO USADO']
    ];

    \Illuminate\Support\Facades\DB::beginTransaction();
    try {
        $estandarIds = [];
        foreach ($categoriasEstandar as $nombreEstandar => $aliasList) {
            $etiqueta = \App\Models\Etiqueta::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreEstandar, 'UTF-8')])->first();
            if (!$etiqueta) {
                $etiqueta = \App\Models\Etiqueta::create([
                    'nombre' => $nombreEstandar,
                    'eliminado' => 1
                ]);
                $this->line(" + Creada etiqueta: '{$nombreEstandar}' (ID {$etiqueta->id})");
            } else {
                $etiqueta->update(['nombre' => $nombreEstandar, 'eliminado' => 1]);
                $this->line(" = Etiqueta estándar activa: '{$nombreEstandar}' (ID {$etiqueta->id})");
            }
            $estandarIds[$nombreEstandar] = $etiqueta->id;
        }

        $todasEtiquetas = \App\Models\Etiqueta::all();
        foreach ($todasEtiquetas as $tag) {
            $tagNombreLower = mb_strtolower(trim($tag->nombre), 'UTF-8');
            $destinoEstandar = null;

            foreach ($categoriasEstandar as $estandar => $aliasList) {
                if ($tagNombreLower === mb_strtolower($estandar, 'UTF-8')) {
                    $destinoEstandar = null;
                    break;
                }
                foreach ($aliasList as $alias) {
                    if ($tagNombreLower === mb_strtolower($alias, 'UTF-8')) {
                        $destinoEstandar = $estandar;
                        break 2;
                    }
                }
            }

            if ($destinoEstandar && isset($estandarIds[$destinoEstandar]) && $tag->id !== $estandarIds[$destinoEstandar]) {
                $nuevoId = $estandarIds[$destinoEstandar];
                $this->comment(" > Fusionando ID {$tag->id} ('{$tag->nombre}') -> ID {$nuevoId} ('{$destinoEstandar}')");

                $relaciones = \Illuminate\Support\Facades\DB::table('etiquetas_productos')->where('clasificacion_id', $tag->id)->get();
                foreach ($relaciones as $rel) {
                    $existe = \Illuminate\Support\Facades\DB::table('etiquetas_productos')
                        ->where('producto_id', $rel->producto_id)
                        ->where('clasificacion_id', $nuevoId)
                        ->exists();

                    if (!$existe) {
                        \Illuminate\Support\Facades\DB::table('etiquetas_productos')
                            ->where('id', $rel->id)
                            ->update(['clasificacion_id' => $nuevoId]);
                    } else {
                        \Illuminate\Support\Facades\DB::table('etiquetas_productos')->where('id', $rel->id)->delete();
                    }
                }

                $tag->update(['eliminado' => 0]);
            }

            if (str_contains($tagNombreLower, 'jefe') || str_contains($tagNombreLower, 'prueba')) {
                $this->warn(" x Desactivando etiqueta no-categoría: ID {$tag->id} ('{$tag->nombre}')");
                $computoId = $estandarIds['cómputo'];
                $relaciones = \Illuminate\Support\Facades\DB::table('etiquetas_productos')->where('clasificacion_id', $tag->id)->get();
                foreach ($relaciones as $rel) {
                    $existe = \Illuminate\Support\Facades\DB::table('etiquetas_productos')
                        ->where('producto_id', $rel->producto_id)
                        ->where('clasificacion_id', $computoId)
                        ->exists();

                    if (!$existe) {
                        \Illuminate\Support\Facades\DB::table('etiquetas_productos')
                            ->where('id', $rel->id)
                            ->update(['clasificacion_id' => $computoId]);
                    } else {
                        \Illuminate\Support\Facades\DB::table('etiquetas_productos')->where('id', $rel->id)->delete();
                    }
                }
                $tag->update(['eliminado' => 0]);
            }
        }

        \Illuminate\Support\Facades\DB::commit();
        $this->info('¡Estandarización de etiquetas completada con éxito!');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        $this->error('Error durante la estandarización: ' . $e->getMessage());
    }
})->purpose('Estandariza las etiquetas del catálogo SGI a las 10 categorías definitivas');

