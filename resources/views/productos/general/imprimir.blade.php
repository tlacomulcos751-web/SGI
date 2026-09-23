<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #fff; color: #000; font-family: 'Inter', Arial, sans-serif; padding-top: 20px; }
        .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 15px; }
        .print-header h2 { color: #2c3e50; font-weight: bold; }
        .table th { background-color: #e9ecef !important; font-weight: bold; color: #2c3e50; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f8f9fa; }
        @media print {
            .no-print { display: none !important; }
            .table-bordered { border: 1px solid #dee2e6 !important; }
            .table-bordered th, .table-bordered td { border: 1px solid #dee2e6 !important; }
            body { padding-top: 0; }
        }
    </style>
</head>
<body onload="setTimeout(() => window.print(), 500)">
    <div class="container">
        <div class="text-end no-print mb-4">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Imprimir</button>
            <button onclick="window.close()" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cerrar</button>
        </div>
        
        <div class="print-header">
            <h2>Reporte de Inventario</h2>
            @if(isset($responsable) && $responsable)
                <h4 class="text-muted">Responsable: {{ $responsable->nombre }} {{ $responsable->apellido }}</h4>
            @else
                <h4 class="text-muted">Todos los Responsables</h4>
            @endif
            <p class="mb-0 text-muted">Fecha de generación: {{ date('d/m/Y H:i') }}</p>
        </div>

        <table class="table table-bordered table-striped table-sm align-middle">
            <thead>
                <tr class="text-center">
                    <th style="width: 5%">#</th>
                    <th style="width: 25%">Producto</th>
                    <th style="width: 10%">Tipo</th>
                    <th style="width: 10%">Precio</th>
                    <th style="width: 10%">Existencia</th>
                    <th style="width: 15%">Ubicación</th>
                    <th style="width: 15%">Empresa</th>
                    <th style="width: 10%">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $index => $producto)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $producto->nombre ?? 'Sin nombre' }}</strong></td>
                    <td class="text-center">{{ ucfirst($producto->tipo ?? 'N/A') }}</td>
                    <td class="text-end">${{ number_format($producto->precio ?? 0, 2) }}</td>
                    <td class="text-center fw-bold">{{ $producto->existencia ?? 0 }} {{ $producto->unidad_medida ?? '' }}</td>
                    <td class="text-center">{{ $producto->ubicacion_nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ $producto->empresa_nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ ucfirst($producto->estado ?? 'N/A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No se encontraron productos con los filtros seleccionados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
