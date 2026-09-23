@forelse ($productosFijos as $index => $producto)
<tr class="hover-scale">
    <td class="ps-4 text-muted">{{ ($productosFijos->currentPage() - 1) * $productosFijos->perPage() + $loop->iteration }}</td>
    <td>
        <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace">
            {{ $producto->clave }}
        </span>
    </td>
    <td>
        <strong>{{ $producto->nombre }}</strong>
        @php
            $etiquetasProducto = $producto->etiquetas_cargadas ?? \App\Models\Producto::etiquetasPorProducto($producto->id_fijo);
        @endphp
        @if(!empty($etiquetasProducto) && count($etiquetasProducto) > 0)
            <div class="mt-1">
                @foreach($etiquetasProducto as $etq)
                    <span class="badge bg-primary bg-opacity-10 text-primary me-1" style="font-size: 0.75rem;">
                        <i class="fas fa-tag me-1"></i>{{ is_object($etq) ? $etq->nombre : $etq['nombre'] }}
                    </span>
                @endforeach
            </div>
        @endif
    </td>
    <td>
        <span class="badge bg-info bg-opacity-10 text-info">
            <i class="fas fa-map-marker-alt me-1"></i> {{ $producto->ubicacion }}
        </span>
    </td>
    <td>
        <span class="badge rounded-pill bg-{{ $producto->estado == 'activo' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $producto->estado == 'activo' ? 'success' : 'secondary' }}">
            {{ ucfirst($producto->estado) }}
        </span>
    </td>
    <td>
        <span class="text-muted">{{ date('d/m/Y', strtotime($producto->fechaEntrada)) }}</span>
    </td>
    <td class="pe-4 text-end">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('productos-fijos.qr', $producto->id) }}" class="btn btn-sm btn-outline-success btn-float" data-bs-toggle="tooltip" title="Ver/Descargar QR">
                <i class="fas fa-qrcode"></i>
            </a>
            <a href="{{ route('productos_fijos.viewFijos',$producto->id)  }}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                <i class="fas fa-eye"></i>
            </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-4 text-muted">
        <i class="fas fa-box-open fa-2x mb-3"></i>
        <p class="mb-0">No se encontraron Activos Fijos registrados</p>
    </td>
</tr>
@endforelse
