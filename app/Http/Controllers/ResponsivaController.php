<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\ProductoFijo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Table;

class ResponsivaController extends Controller
{
    /**
     * Genera la Carta Responsiva / Carta Poder para un Activo Fijo individual.
     */
    public function generarFijo(Request $request, $id)
    {
        if ((!tienePermiso('fijos - leer') && !esSuperAdmin()) || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios para emitir la responsiva.');
        }

        try {
            $fijo = ProductoFijo::with([
                'producto.empresa',
                'producto.ubicacion',
                'producto.etiquetas',
                'usuarioResponsable.rol'
            ])->findOrFail($id);

            $producto = $fijo->producto;
            $responsable = $fijo->usuarioResponsable;
            $empresa = ($producto && $producto->empresa) ? $producto->empresa : Empresa::first();

            // Preparar arreglo estructurado de bienes
            $bienes = [[
                'id' => $fijo->id,
                'clave' => $fijo->clave ?? 'S/C',
                'tipo' => 'Activo Fijo',
                'nombre' => $producto ? $producto->nombre : 'Activo Fijo #' . $fijo->id,
                'descripcion' => $producto ? ($producto->descripcion ?? 'N/A') : 'N/A',
                'marca' => $producto ? ($producto->marca ?? 'N/A') : 'N/A',
                'modelo' => $producto ? ($producto->modelo ?? 'N/A') : 'N/A',
                'serie' => ($producto && !empty($producto->codigoBarra)) ? $producto->codigoBarra : ($fijo->clave ?? 'S/N'),
                'codigo_barra' => $producto ? $producto->codigoBarra : null,
                'ubicacion' => ($producto && $producto->ubicacion) ? $producto->ubicacion->nombre : 'N/A',
                'estado' => ucfirst($fijo->estado ?? 'Bueno'),
                'calidad' => ucfirst($fijo->calidad ?? 'Bueno'),
                'precio' => ($producto && $producto->precio !== null && $producto->precio > 0)
                    ? '$' . number_format($producto->precio, 2)
                    : 'N/A (Sin costo)',
                'categoria' => ($producto && $producto->etiquetas && $producto->etiquetas->count() > 0)
                    ? $producto->etiquetas->pluck('nombre')->join(', ')
                    : 'General',
            ]];

            $datos = $this->prepararDatosResponsiva(
                $empresa,
                $responsable,
                $bienes,
                'INDIVIDUAL - ACTIVO FIJO',
                $fijo->clave ?? ('ACT-' . $fijo->id)
            );

            if ($request->query('formato') === 'word') {
                return $this->descargarWord($datos);
            }

            return view('responsivas.imprimir', $datos);
        } catch (\Exception $e) {
            Log::error('Error al generar responsiva de activo fijo #' . $id . ': ' . $e->getMessage());
            return back()->with('error', 'Error al generar la carta responsiva: ' . $e->getMessage());
        }
    }

    /**
     * Genera la Carta Responsiva / Carta Poder para un Vehículo individual.
     */
    public function generarVehiculo(Request $request, $id)
    {
        if ((!tienePermiso('vehiculo - leer') && !esSuperAdmin()) || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios para emitir la responsiva.');
        }

        try {
            $vehiculo = Vehiculo::with(['ubicacion', 'usuarioResponsable.rol'])->findOrFail($id);
            $responsable = $vehiculo->usuarioResponsable;
            $empresa = Empresa::first(); // Los vehículos usan la empresa interna asignada o por defecto

            $bienes = [[
                'id' => $vehiculo->id,
                'clave' => $vehiculo->placas ?? 'S/P',
                'tipo' => 'Vehículo Utilitario',
                'nombre' => trim($vehiculo->tipo . ' ' . $vehiculo->marca . ' ' . $vehiculo->modelo),
                'descripcion' => "Color: {$vehiculo->color}, Carrocería: {$vehiculo->carroceria}, Versión: {$vehiculo->version}",
                'marca' => $vehiculo->marca ?? 'N/A',
                'modelo' => $vehiculo->modelo ?? 'N/A',
                'serie' => $vehiculo->niv ?? 'N/A',
                'codigo_barra' => $vehiculo->placas ?? null,
                'ubicacion' => $vehiculo->ubicacion ? $vehiculo->ubicacion->nombre : 'N/A',
                'estado' => ucfirst($vehiculo->status ?? 'Activo'),
                'calidad' => 'Operativo',
                'precio' => 'N/A (Bajo resguardo institucional)',
                'categoria' => 'Flotilla Vehicular',
            ]];

            $datos = $this->prepararDatosResponsiva(
                $empresa,
                $responsable,
                $bienes,
                'INDIVIDUAL - VEHÍCULO',
                $vehiculo->placas ?? ('VEH-' . $vehiculo->id)
            );

            if ($request->query('formato') === 'word') {
                return $this->descargarWord($datos);
            }

            return view('responsivas.imprimir', $datos);
        } catch (\Exception $e) {
            Log::error('Error al generar responsiva de vehículo #' . $id . ': ' . $e->getMessage());
            return back()->with('error', 'Error al generar la carta responsiva de vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Genera la Carta Responsiva Consolidada para todos los bienes asignados a un Colaborador.
     */
    public function generarPorResponsable(Request $request, $id)
    {
        if ((!tienePermiso('leerUsuarios') && !esSuperAdmin()) || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios para emitir la responsiva.');
        }

        try {
            $responsable = Usuario::with('rol')->findOrFail($id);

            // Consultar activos fijos asignados
            $fijos = ProductoFijo::with([
                'producto.empresa',
                'producto.ubicacion',
                'producto.etiquetas'
            ])
            ->where('responsable', $id)
            ->where('estado', '!=', 'baja')
            ->get();

            // Consultar vehículos asignados
            $vehiculos = Vehiculo::with('ubicacion')
                ->where('responsable', $id)
                ->get();

            if ($fijos->isEmpty() && $vehiculos->isEmpty()) {
                return back()->with('info', 'El colaborador ' . $responsable->nombreCompleto() . ' no tiene bienes asignados a su cargo actualmente.');
            }

            // Determinar empresa emisora principal (de los productos asignados o la primera de la empresa)
            $empresaId = $request->query('empresa_id');
            if ($empresaId) {
                $empresa = Empresa::find($empresaId) ?? Empresa::first();
            } else {
                $primerFijoConEmpresa = $fijos->first(fn($f) => $f->producto && $f->producto->empresa);
                $empresa = ($primerFijoConEmpresa && $primerFijoConEmpresa->producto->empresa)
                    ? $primerFijoConEmpresa->producto->empresa
                    : Empresa::first();
            }

            $bienes = [];

            // Agregar activos fijos
            foreach ($fijos as $fijo) {
                $p = $fijo->producto;
                $bienes[] = [
                    'id' => $fijo->id,
                    'clave' => $fijo->clave ?? 'S/C',
                    'tipo' => 'Activo Fijo',
                    'nombre' => $p ? $p->nombre : 'Activo #' . $fijo->id,
                    'descripcion' => $p ? ($p->descripcion ?? 'N/A') : 'N/A',
                    'marca' => $p ? ($p->marca ?? 'N/A') : 'N/A',
                    'modelo' => $p ? ($p->modelo ?? 'N/A') : 'N/A',
                    'serie' => ($p && !empty($p->codigoBarra)) ? $p->codigoBarra : ($fijo->clave ?? 'S/N'),
                    'codigo_barra' => $p ? $p->codigoBarra : null,
                    'ubicacion' => ($p && $p->ubicacion) ? $p->ubicacion->nombre : 'N/A',
                    'estado' => ucfirst($fijo->estado ?? 'Bueno'),
                    'calidad' => ucfirst($fijo->calidad ?? 'Bueno'),
                    'precio' => ($p && $p->precio !== null && $p->precio > 0)
                        ? '$' . number_format($p->precio, 2)
                        : 'N/A',
                    'categoria' => ($p && $p->etiquetas && $p->etiquetas->count() > 0)
                        ? $p->etiquetas->pluck('nombre')->join(', ')
                        : 'General',
                ];
            }

            // Agregar vehículos
            foreach ($vehiculos as $v) {
                $bienes[] = [
                    'id' => $v->id,
                    'clave' => $v->placas ?? 'S/P',
                    'tipo' => 'Vehículo',
                    'nombre' => trim($v->tipo . ' ' . $v->marca . ' ' . $v->modelo),
                    'descripcion' => "Color: {$v->color}, Carrocería: {$v->carroceria}, Versión: {$v->version}",
                    'marca' => $v->marca ?? 'N/A',
                    'modelo' => $v->modelo ?? 'N/A',
                    'serie' => $v->niv ?? 'N/A',
                    'codigo_barra' => $v->placas ?? null,
                    'ubicacion' => $v->ubicacion ? $v->ubicacion->nombre : 'N/A',
                    'estado' => ucfirst($v->status ?? 'Activo'),
                    'calidad' => 'Operativo',
                    'precio' => 'N/A',
                    'categoria' => 'Vehículo',
                ];
            }

            $datos = $this->prepararDatosResponsiva(
                $empresa,
                $responsable,
                $bienes,
                'CONSOLIDADA POR COLABORADOR',
                'USR-' . $responsable->id
            );

            if ($request->query('formato') === 'word') {
                return $this->descargarWord($datos);
            }

            return view('responsivas.imprimir', $datos);
        } catch (\Exception $e) {
            Log::error('Error al generar responsiva consolidada para el usuario #' . $id . ': ' . $e->getMessage());
            return back()->with('error', 'Error al generar la carta responsiva: ' . $e->getMessage());
        }
    }

    /**
     * Prepara el arreglo estandarizado de datos para la plantilla y para Word.
     */
    private function prepararDatosResponsiva($empresa, $responsable, array $bienes, string $tipoResponsiva, string $claveRef): array
    {
        $fechaActual = now();
        $folio = 'CR-' . $fechaActual->format('Ym') . '-' . strtoupper(substr(md5($claveRef . $fechaActual->timestamp), 0, 5));

        return [
            'empresa' => $empresa,
            'responsable' => $responsable,
            'bienes' => $bienes,
            'tipoResponsiva' => $tipoResponsiva,
            'folio' => $folio,
            'fechaEmision' => $fechaActual->format('d/m/Y'),
            'horaEmision' => $fechaActual->format('H:i') . ' hrs',
            'lugarEmision' => 'Puebla, Pue., México',
            'usuarioEmisor' => Session::get('usuario'),
            'nombreEmisor' => nombreCompletoUsuario(),
            'totalBienes' => count($bienes),
        ];
    }

    /**
     * Genera y descarga el archivo Word (.docx) formalmente maquetado.
     */
    private function descargarWord(array $datos)
    {
        $phpWord = new PhpWord();

        // Configuración de página Carta / Letter con márgenes compactos
        $section = $phpWord->addSection([
            'marginTop' => 720,    // 0.5 in
            'marginBottom' => 720,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);

        $empresaNombre = $datos['empresa'] ? $datos['empresa']->nombre : 'EMPRESA';
        $empresaRazon = $datos['empresa'] ? ($datos['empresa']->nombre_registrado ?: $empresaNombre) : $empresaNombre;
        $empresaRfc = $datos['empresa'] ? ($datos['empresa']->rfc ?: 'N/A') : 'N/A';
        $empresaDir = $datos['empresa'] ? ($datos['empresa']->direccion ?: 'Dirección conocida') : 'Dirección conocida';

        // 1. Encabezado Oficial
        $section->addText(
            strtoupper($empresaRazon),
            ['bold' => true, 'size' => 14, 'color' => '1E3A8A'],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            "RFC: {$empresaRfc} | {$empresaDir}",
            ['size' => 8.5, 'color' => '555555', 'italic' => true],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        // Título del Documento
        $section->addText(
            'CARTA RESPONSIVA DE ASIGNACIÓN Y RESGUARDO DE BIENES',
            ['bold' => true, 'size' => 12, 'color' => '0F172A'],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            "CARTA PODER DE CUSTODIA Y USO EXCLUSIVO LABORAL | FOLIO: {$datos['folio']}",
            ['bold' => true, 'size' => 9, 'color' => '2563EB'],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        // 2. Tabla de Metadatos (Empresa, Colaborador, Fecha)
        $metaTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'CBD5E1',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER,
        ]);

        $metaTable->addRow();
        $metaTable->addCell(2200, ['bgColor' => 'F1F5F9'])->addText('Fecha de Emisión:', ['bold' => true, 'size' => 8.5]);
        $metaTable->addCell(3000)->addText("{$datos['fechaEmision']} ({$datos['horaEmision']})", ['size' => 8.5]);
        $metaTable->addCell(2000, ['bgColor' => 'F1F5F9'])->addText('Lugar de Asignación:', ['bold' => true, 'size' => 8.5]);
        $metaTable->addCell(3000)->addText($datos['lugarEmision'], ['size' => 8.5]);

        $respNombre = $datos['responsable'] ? $datos['responsable']->nombreCompleto() : 'Sin Asignar (Resguardo en Almacén)';
        $respPuesto = ($datos['responsable'] && $datos['responsable']->rol) ? $datos['responsable']->rol->nombre : 'Colaborador';
        $respCorreo = $datos['responsable'] ? $datos['responsable']->gmail : 'N/A';

        $metaTable->addRow();
        $metaTable->addCell(2200, ['bgColor' => 'F1F5F9'])->addText('Colaborador Asignado:', ['bold' => true, 'size' => 8.5]);
        $metaTable->addCell(3000)->addText($respNombre, ['bold' => true, 'size' => 8.5, 'color' => '1E3A8A']);
        $metaTable->addCell(2000, ['bgColor' => 'F1F5F9'])->addText('Puesto / Cargo:', ['bold' => true, 'size' => 8.5]);
        $metaTable->addCell(3000)->addText($respPuesto, ['size' => 8.5]);

        $metaTable->addRow();
        $metaTable->addCell(2200, ['bgColor' => 'F1F5F9'])->addText('Correo Electrónico:', ['bold' => true, 'size' => 8.5]);
        $metaTable->addCell(3000)->addText($respCorreo, ['size' => 8.5]);
        $metaTable->addCell(2000, ['bgColor' => 'F1F5F9'])->addText('Total de Bienes:', ['bold' => true, 'size' => 8.5]);
        $metaTable->addCell(3000)->addText($datos['totalBienes'] . ' artículo(s) inventariado(s)', ['bold' => true, 'size' => 8.5]);

        $section->addTextBreak(1);

        // Declaración introductoria
        $section->addText(
            "Por medio del presente instrumento legal, la empresa {$empresaRazon} hace constar la asignación formal bajo estricta custodia y resguardo temporal de los bienes y herramientas de trabajo que a continuación se detallan, a favor de {$respNombre}, quien manifiesta recibirlos en óptimas condiciones de operación y conservación:",
            ['size' => 9],
            ['alignment' => Jc::BOTH]
        );

        $section->addTextBreak(1);

        // 3. Tabla Detallada de Bienes
        $itemsTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'CBD5E1',
            'cellMargin' => 70,
            'alignment' => JcTable::CENTER,
        ]);

        // Cabecera de la tabla
        $itemsTable->addRow();
        $itemsTable->addCell(500, ['bgColor' => '1E3A8A'])->addText('#', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $itemsTable->addCell(1600, ['bgColor' => '1E3A8A'])->addText('Clave', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF']);
        $itemsTable->addCell(2400, ['bgColor' => '1E3A8A'])->addText('Descripción / Bien', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF']);
        $itemsTable->addCell(1600, ['bgColor' => '1E3A8A'])->addText('Marca / Modelo', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF']);
        $itemsTable->addCell(1600, ['bgColor' => '1E3A8A'])->addText('Serie / Código', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF']);
        $itemsTable->addCell(1200, ['bgColor' => '1E3A8A'])->addText('Estado', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $itemsTable->addCell(1300, ['bgColor' => '1E3A8A'])->addText('Valor Est.', ['bold' => true, 'size' => 8, 'color' => 'FFFFFF'], ['alignment' => Jc::RIGHT]);

        foreach ($datos['bienes'] as $idx => $b) {
            $rowBg = ($idx % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
            $itemsTable->addRow();
            $itemsTable->addCell(500, ['bgColor' => $rowBg])->addText((string)($idx + 1), ['size' => 8], ['alignment' => Jc::CENTER]);
            $itemsTable->addCell(1600, ['bgColor' => $rowBg])->addText($b['clave'], ['bold' => true, 'size' => 8]);
            $itemsTable->addCell(2400, ['bgColor' => $rowBg])->addText($b['nombre'] . "\n(" . $b['categoria'] . ")", ['size' => 8]);
            $itemsTable->addCell(1600, ['bgColor' => $rowBg])->addText("{$b['marca']} / {$b['modelo']}", ['size' => 8]);
            $itemsTable->addCell(1600, ['bgColor' => $rowBg])->addText($b['serie'], ['size' => 8]);
            $itemsTable->addCell(1200, ['bgColor' => $rowBg])->addText($b['estado'], ['size' => 8], ['alignment' => Jc::CENTER]);
            $itemsTable->addCell(1300, ['bgColor' => $rowBg])->addText($b['precio'], ['size' => 8], ['alignment' => Jc::RIGHT]);
        }

        $section->addTextBreak(1);

        // 4. Cláusulas y Términos Legales
        $section->addText('CLÁUSULAS Y TÉRMINOS DE RESGUARDO Y RESPONSABILIDAD:', ['bold' => true, 'size' => 9, 'color' => '0F172A']);

        $clausulas = [
            'PRIMERA (PROPIEDAD Y DESTINO LABORAL): El COLABORADOR reconoce y acepta expresamente que los bienes descritos son propiedad exclusiva de LA EMPRESA, y le son entregados en comodato y custodia temporal como herramientas de trabajo indispensables para el desarrollo de sus funciones laborales contratadas.',
            'SEGUNDA (CUSTODIA Y BUEN USO): El COLABORADOR se compromete a salvaguardar, vigilar y mantener en óptimas condiciones los bienes asignados, utilizándolos de manera adecuada y prohibiéndose expresamente su comercialización, cesión, renta, empeño, préstamo o traslado a personas o sitios no autorizados.',
            'TERCERA (FALLAS Y MANTENIMIENTO): Ante cualquier falla técnica, desperfecto o necesidad de mantenimiento preventivo o correctivo, el COLABORADOR se obliga a notificar de inmediato al área correspondiente de Administración o Sistemas de LA EMPRESA.',
            'CUARTA (EXTRAVÍO, ROBO O DAÑO IMPUTABLE): En caso de daño por negligencia, robo o extravío de los bienes, el COLABORADOR se obliga a informar por escrito a LA EMPRESA dentro de las 24 horas siguientes y colaborar en la formulación de las denuncias correspondientes ante las autoridades competentes.',
            'QUINTA (RESTITUCIÓN Y DEVOLUCIÓN): Al concluir la relación de trabajo, por cambio de puesto o en cualquier momento que LA EMPRESA así lo determine formalmente, el COLABORADOR deberá restituir íntegramente los bienes señalados en el mismo estado en que los recibió, salvo el desgaste natural derivado del uso ordinario.'
        ];

        foreach ($clausulas as $cl) {
            $section->addText($cl, ['size' => 8], ['alignment' => Jc::BOTH]);
        }

        $section->addTextBreak(1);

        // 5. Tabla de Firmas
        $signTable = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 100,
            'alignment' => JcTable::CENTER,
        ]);

        $signTable->addRow();
        $cellEntrega = $signTable->addCell(5100, ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'bgColor' => 'F8FAFC']);
        $cellRecibe = $signTable->addCell(5100, ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'bgColor' => 'F8FAFC']);

        $cellEntrega->addText('ENTREGA CONFORME:', ['bold' => true, 'size' => 8.5, 'color' => '475569'], ['alignment' => Jc::CENTER]);
        $cellEntrega->addTextBreak(3);
        $cellEntrega->addText('_______________________________________', ['color' => '94A3B8'], ['alignment' => Jc::CENTER]);
        $cellEntrega->addText($datos['nombreEmisor'], ['bold' => true, 'size' => 8.5], ['alignment' => Jc::CENTER]);
        $cellEntrega->addText('Administración / Control de Inventarios', ['size' => 8, 'color' => '64748B'], ['alignment' => Jc::CENTER]);

        $cellRecibe->addText('RECIBE DE CONFORMIDAD:', ['bold' => true, 'size' => 8.5, 'color' => '475569'], ['alignment' => Jc::CENTER]);
        $cellRecibe->addTextBreak(3);
        $cellRecibe->addText('_______________________________________', ['color' => '94A3B8'], ['alignment' => Jc::CENTER]);
        $cellRecibe->addText($respNombre, ['bold' => true, 'size' => 8.5], ['alignment' => Jc::CENTER]);
        $cellRecibe->addText("Firma del Colaborador ({$respPuesto})", ['size' => 8, 'color' => '64748B'], ['alignment' => Jc::CENTER]);

        // Guardar y descargar archivo temporal
        $sanitizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', $respNombre));
        $fileName = "Carta_Responsiva_{$sanitizedName}_" . now()->format('Ymd') . '.docx';
        $tempDir = storage_path('app/temp');
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
