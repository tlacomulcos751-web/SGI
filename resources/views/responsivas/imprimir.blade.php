<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Responsiva - {{ $folio }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- JsBarcode para renderizado visual de códigos de barras -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Barra de acciones flotante (solo en pantalla) */
        .action-bar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            color: #ffffff;
            position: sticky;
            top: 0;
            z-index: 1050;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        /* Hoja de papel Carta */
        .paper-sheet {
            max-width: 900px;
            margin: 25px auto;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-radius: 8px;
            padding: 40px 45px;
            position: relative;
        }

        .border-navy {
            border-color: #1e3a8a !important;
        }

        .text-navy {
            color: #1e3a8a !important;
        }

        .bg-navy {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
        }

        .bg-navy-subtle {
            background-color: #f8fafc !important;
            border-left: 3px solid #1e3a8a;
        }

        .table-custom th {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .table-custom td {
            font-size: 0.82rem;
            padding: 8px 10px;
            vertical-align: middle;
            border-color: #e2e8f0;
        }

        .legal-clause {
            font-size: 0.74rem;
            line-height: 1.45;
            text-align: justify;
            color: #334155;
            margin-bottom: 6px;
        }

        .signature-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 15px;
            background: #f8fafc;
            min-height: 135px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            margin: 45px 25px 8px 25px;
        }

        .barcode-mini {
            max-height: 38px;
            max-width: 140px;
        }

        /* Reglas de impresión */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .paper-sheet {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            @page {
                size: letter;
                margin: 12mm 15mm 12mm 15mm;
            }

            tr {
                page-break-inside: avoid;
            }

            .signature-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de herramientas (Oculta al imprimir) -->
    <div class="action-bar py-3 px-4 no-print">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary px-3 py-2 fs-6">
                    <i class="fas fa-file-signature me-1"></i> Folio: {{ $folio }}
                </span>
                <span class="text-white-50 small d-none d-md-inline">
                    {{ $tipoResponsiva }} &bull; {{ $totalBienes }} bien(es) inventariado(s)
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-light btn-sm fw-semibold shadow-sm px-3" onclick="window.print()">
                    <i class="fas fa-print me-1 text-primary"></i> Imprimir / Guardar como PDF
                </button>
                <a href="{{ request()->fullUrlWithQuery(['formato' => 'word']) }}" class="btn btn-outline-info btn-sm fw-semibold shadow-sm text-white px-3">
                    <i class="fas fa-file-word me-1"></i> Descargar Word (.docx)
                </a>
                <button type="button" class="btn btn-outline-light btn-sm shadow-sm" onclick="window.close()">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Hoja Carta Imprimible -->
    <div class="paper-sheet">
        <!-- 1. Encabezado Oficial -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                @if($empresa && $empresa->fotografias)
                    <img src="{{ asset('imagenes/empresas/' . $empresa->fotografias) }}" alt="Logo Empresa" style="max-height: 60px; max-width: 140px; object-fit: contain;" onerror="this.style.display='none'">
                @endif
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0 text-uppercase" style="letter-spacing: -0.3px;">
                        {{ $empresa->nombre_registrado ?: ($empresa->nombre ?: 'EMPRESA') }}
                    </h2>
                    <div class="text-muted small mt-1">
                        <strong>RFC:</strong> {{ $empresa->rfc ?: 'N/A' }} &bull; 
                        <strong>Dirección:</strong> {{ $empresa->direccion ?: 'Domicilio Fiscal Conocido' }}
                    </div>
                </div>
            </div>
            <div class="text-end">
                <div class="badge bg-navy px-3 py-2 fs-6 mb-1">
                    {{ $folio }}
                </div>
                <div class="text-muted small">
                    <strong>Fecha:</strong> {{ $fechaEmision }} &bull; {{ $horaEmision }}
                </div>
            </div>
        </div>

        <!-- 2. Título Formal del Documento -->
        <div class="text-center my-3">
            <h1 class="h6 fw-bold text-uppercase text-navy mb-1" style="letter-spacing: 0.5px;">
                CARTA RESPONSIVA DE ASIGNACIÓN Y RESGUARDO DE BIENES
            </h1>
            <p class="small text-muted mb-0">
                CARTA PODER Y COMPROMISO DE CUSTODIA DE HERRAMIENTAS DE TRABAJO (PROPIEDAD DE LA EMPRESA)
            </p>
        </div>

        <!-- 3. Metadatos de la Asignación y del Receptor -->
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                <tbody>
                    <tr>
                        <th class="bg-light text-navy" style="width: 22%;">Colaborador Asignado:</th>
                        <td class="fw-bold text-navy" style="width: 38%;">
                            {{ $responsable ? $responsable->nombreCompleto() : 'Sin Asignar (Resguardo Almacén)' }}
                        </td>
                        <th class="bg-light text-navy" style="width: 18%;">Puesto / Rol:</th>
                        <td style="width: 22%;">
                            {{ ($responsable && $responsable->rol) ? $responsable->rol->nombre : 'Colaborador' }}
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light text-navy">Correo Electrónico:</th>
                        <td>{{ $responsable ? $responsable->gmail : 'N/A' }}</td>
                        <th class="bg-light text-navy">Lugar de Emisión:</th>
                        <td>{{ $lugarEmision }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-navy">Empresa Propietaria:</th>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $empresa->nombre }}</span> ({{ $empresa->rfc }})</td>
                        <th class="bg-light text-navy">Total de Bienes:</th>
                        <td><span class="fw-bold text-navy">{{ $totalBienes }}</span> artículo(s) inventariado(s)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Declaración de entrega -->
        <div class="p-2 mb-3 bg-navy-subtle rounded-2">
            <p class="small text-secondary mb-0" style="font-size: 0.78rem; text-align: justify;">
                Por medio del presente instrumento legal, <strong>{{ $empresa->nombre_registrado ?: $empresa->nombre }}</strong> hace constar la entrega formal bajo resguardo y custodia temporal de los bienes y herramientas de trabajo que a continuación se detallan, a favor de <strong>{{ $responsable ? $responsable->nombreCompleto() : 'EL COLABORADOR' }}</strong>, quien manifiesta recibirlos en óptimas condiciones de operación y conservación para el desempeño exclusivo de sus funciones laborales:
            </p>
        </div>

        <!-- 4. Tabla Detallada de Bienes Asignados -->
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered table-striped table-custom mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 4%;">#</th>
                        <th style="width: 16%;">Clave</th>
                        <th style="width: 26%;">Descripción / Bien</th>
                        <th style="width: 16%;">Marca / Modelo</th>
                        <th style="width: 18%;">Serie / Código</th>
                        <th class="text-center" style="width: 9%;">Estado</th>
                        <th class="text-end" style="width: 11%;">Costo Est.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bienes as $index => $b)
                    <tr>
                        <td class="text-center fw-medium">{{ $index + 1 }}</td>
                        <td class="fw-bold text-navy">
                            {{ $b['clave'] }}
                            @if(!empty($b['codigo_barra']))
                                <div>
                                    <svg class="barcode-svg barcode-mini" data-code="{{ $b['codigo_barra'] }}"></svg>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $b['nombre'] }}</div>
                            <small class="text-muted d-block" style="font-size: 0.73rem;">
                                Cat: <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $b['categoria'] }}</span>
                                @if(!empty($b['ubicacion']) && $b['ubicacion'] !== 'N/A')
                                    &bull; Ubic: {{ $b['ubicacion'] }}
                                @endif
                            </small>
                        </td>
                        <td>
                            <div>{{ $b['marca'] }}</div>
                            <small class="text-muted">{{ $b['modelo'] }}</small>
                        </td>
                        <td>
                            <span class="font-monospace small">{{ $b['serie'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-10 text-success p-1" style="font-size: 0.72rem;">
                                {{ $b['estado'] }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            {{ $b['precio'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 5. Cláusulas y Términos Legales -->
        <div class="border rounded p-2 mb-3 bg-light">
            <div class="fw-bold text-navy small mb-1">
                <i class="fas fa-balance-scale me-1"></i> CLÁUSULAS DE RESGUARDO, CUSTODIA Y RESPONSABILIDAD:
            </div>
            <div class="legal-clause">
                <strong>PRIMERA (PROPIEDAD Y DESTINO LABORAL):</strong> El COLABORADOR reconoce y acepta expresamente que los bienes descritos son propiedad exclusiva de LA EMPRESA, y le son entregados en calidad de comodato y custodia temporal como herramientas indispensables para el desarrollo de sus funciones laborales contratadas.
            </div>
            <div class="legal-clause">
                <strong>SEGUNDA (CUSTODIA Y BUEN USO):</strong> El COLABORADOR se compromete a salvaguardar, vigilar y mantener en óptimas condiciones los bienes asignados, utilizándolos de manera adecuada y prohibiéndose expresamente su comercialización, cesión, renta, empeño, préstamo o traslado a personas o sitios no autorizados.
            </div>
            <div class="legal-clause">
                <strong>TERCERA (FALLAS Y MANTENIMIENTO):</strong> Ante cualquier falla técnica, desperfecto o necesidad de mantenimiento preventivo o correctivo, el COLABORADOR se obliga a notificar de inmediato al área correspondiente de Administración o Sistemas de LA EMPRESA.
            </div>
            <div class="legal-clause">
                <strong>CUARTA (EXTRAVÍO, ROBO O DAÑO IMPUTABLE):</strong> En caso de daño por negligencia, robo o extravío de los bienes, el COLABORADOR se obliga a informar por escrito a LA EMPRESA dentro de las 24 horas siguientes y colaborar en la formulación de las denuncias correspondientes ante las autoridades competentes.
            </div>
            <div class="legal-clause">
                <strong>QUINTA (RESTITUCIÓN Y DEVOLUCIÓN):</strong> Al concluir la relación de trabajo, por cambio de puesto o en cualquier momento que LA EMPRESA así lo determine formalmente, el COLABORADOR deberá restituir íntegramente los bienes señalados en el mismo estado en que los recibió, salvo el desgaste natural derivado del uso ordinario.
            </div>
        </div>

        <!-- 6. Firmas Formales -->
        <div class="signature-section mt-4">
            <div class="row g-3">
                <div class="col-6">
                    <div class="signature-box text-center">
                        <div class="fw-bold text-navy small">ENTREGA CONFORME:</div>
                        <div class="signature-line"></div>
                        <div class="fw-bold small">{{ $nombreEmisor }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Administración / Control de Inventarios</div>
                        <div class="text-muted" style="font-size: 0.68rem;">Fecha: {{ $fechaEmision }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="signature-box text-center">
                        <div class="fw-bold text-navy small">RECIBE DE CONFORMIDAD:</div>
                        <div class="signature-line"></div>
                        <div class="fw-bold small">{{ $responsable ? $responsable->nombreCompleto() : 'EL COLABORADOR' }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Firma del Colaborador ({{ ($responsable && $responsable->rol) ? $responsable->rol->nombre : 'Receptor' }})</div>
                        <div class="text-muted" style="font-size: 0.68rem;">Acepto los términos y responsabilidades estipulados</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie de página oficial -->
        <div class="text-center text-muted border-top pt-2 mt-3" style="font-size: 0.68rem;">
            Documento emitido electrónicamente por el Sistema de Gestión de Inventario (SGI) &bull; Folio único de validación: {{ $folio }}
        </div>
    </div>

    <!-- Script de Inicialización de Códigos de Barras -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof JsBarcode !== 'undefined') {
                document.querySelectorAll('.barcode-svg').forEach(function(svg) {
                    var code = svg.getAttribute('data-code');
                    if (code && code.trim() !== '') {
                        try {
                            JsBarcode(svg, code.trim(), {
                                format: "CODE128",
                                width: 1.1,
                                height: 26,
                                displayValue: false,
                                margin: 0
                            });
                        } catch (e) {
                            console.debug('Error renderizando código en responsiva:', code, e);
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
