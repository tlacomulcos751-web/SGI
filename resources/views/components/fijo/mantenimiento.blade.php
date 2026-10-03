@php
use Carbon\Carbon;
$mantenimientos = $productoFijo->mantenimientos ?? collect();
$ultimoMantenimiento = $mantenimientos->first();
$hoy = Carbon::today();
@endphp

<div class="card border border-slate-200 rounded-xl shadow-xs overflow-hidden mb-4 bg-white" id="mantenimientos-fijos-container">
    <div class="px-5 py-3.5 border-b border-slate-100 d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white">
        <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0 font-semibold text-slate-900 text-sm d-flex align-items-center gap-2">
                <iconify-icon icon="lucide:wrench" width="16" height="16" class="text-slate-700"></iconify-icon>
                <span>Historial de Mantenimiento Preventivo</span>
            </h6>
            <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                {{ $mantenimientos->count() }} {{ $mantenimientos->count() === 1 ? 'servicio' : 'servicios' }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('productos.fijos.generarMantenimiento', $productoFijo->id) }}" 
               class="btn-clean-secondary text-xs py-1.5 px-3 text-decoration-none d-inline-flex align-items-center gap-1.5"
               title="Descargar Orden de Mantenimiento oficial en Word (.docx)">
                <iconify-icon icon="lucide:file-down" width="14" height="14" class="text-primary"></iconify-icon>
                <span>Descargar Orden Mnt.</span>
            </a>

            @if(tienePermiso('fijos - modificar') && $productoFijo->estado != 'baja')
            <button class="btn-clean-primary text-xs py-1.5 px-3" 
                    data-bs-toggle="modal" data-bs-target="#modalAgregarMantenimientoFijo">
                <iconify-icon icon="lucide:plus" width="14" height="14"></iconify-icon>
                <span>Registrar servicio</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Resumen de Periodicidad / Próximo Servicio -->
    @if($ultimoMantenimiento)
    @php
        $proxima = $ultimoMantenimiento->proxima_fecha ? Carbon::parse($ultimoMantenimiento->proxima_fecha) : null;
        $semaforo = $ultimoMantenimiento->semaforo;
        $dias = $ultimoMantenimiento->dias_restantes;
    @endphp
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
        <div class="row g-3 align-items-center text-xs">
            <!-- Último servicio -->
            <div class="col-sm-6 col-md-4">
                <div class="text-slate-500">Último servicio realizado:</div>
                <div class="font-semibold text-slate-900 font-mono mt-0.5">
                    {{ Carbon::parse($ultimoMantenimiento->fecha)->format('d/m/Y') }}
                </div>
            </div>

            <!-- Frecuencia -->
            <div class="col-sm-6 col-md-4">
                <div class="text-slate-500">Periodicidad:</div>
                <div class="font-medium text-slate-800 mt-0.5">
                    Cada {{ $ultimoMantenimiento->frecuencia_meses ?? 6 }} meses
                </div>
            </div>

            <!-- Próximo servicio y Semáforo -->
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center justify-content-md-end">
                    @if($proxima)
                        @if($semaforo === 'vencido')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                <iconify-icon icon="lucide:alert-circle" width="13" height="13"></iconify-icon>
                                Vencido ({{ $proxima->format('d/m/Y') }})
                            </span>
                        @elseif($semaforo === 'urgente')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                <iconify-icon icon="lucide:clock" width="13" height="13"></iconify-icon>
                                En {{ $dias }} días ({{ $proxima->format('d/m/Y') }})
                            </span>
                        @elseif($semaforo === 'proximo')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                <iconify-icon icon="lucide:calendar" width="13" height="13"></iconify-icon>
                                Programado: {{ $proxima->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <iconify-icon icon="lucide:check-circle-2" width="13" height="13"></iconify-icon>
                                Al día (Próximo: {{ $proxima->format('d/m/Y') }})
                            </span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Sin fecha programada</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="p-5">
        @if($mantenimientos->isEmpty())
        <div class="text-center py-6">
            <iconify-icon icon="lucide:wrench" width="28" height="28" class="text-slate-300 mb-2"></iconify-icon>
            <p class="text-sm font-medium text-slate-700 mb-1">Sin historial de mantenimiento preventivo</p>
            <p class="text-xs text-slate-400 mb-3" style="max-width: 420px; margin: 0 auto;">
                Este activo no cuenta con servicios registrados. Agrega el primer mantenimiento para llevar control de su periodicidad.
            </p>
            @if(tienePermiso('fijos - modificar') && $productoFijo->estado != 'baja')
            <button class="btn-clean-secondary text-xs" data-bs-toggle="modal" data-bs-target="#modalAgregarMantenimientoFijo">
                <iconify-icon icon="lucide:plus" width="13" height="13" class="me-1"></iconify-icon>
                <span>Registrar primer servicio</span>
            </button>
            @endif
        </div>
        @else
        <div class="space-y-3">
            @foreach($mantenimientos as $item)
            @php
                $itemProxima = $item->proxima_fecha ? Carbon::parse($item->proxima_fecha) : null;
            @endphp
            <div class="p-3.5 border border-slate-100 rounded-lg bg-white hover:border-slate-300 transition-colors">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-2 pb-2 border-b border-slate-100">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="font-medium text-slate-900 text-sm">
                                {{ $item->tipo_servicio }}
                            </span>
                            <span class="text-xs text-slate-500 font-mono bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded">
                                Cada {{ $item->frecuencia_meses ?? 6 }}m
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 d-flex align-items-center flex-wrap gap-2">
                            <span>Realizado: <strong class="text-slate-700 font-mono">{{ Carbon::parse($item->fecha)->format('d/m/Y') }}</strong></span>
                            @if($item->tecnico)
                                <span class="text-slate-300">•</span>
                                <span>Técnico: <strong class="text-slate-700">{{ $item->tecnico }}</strong></span>
                            @endif
                            @if($item->costo !== null && $item->costo > 0)
                                <span class="text-slate-300">•</span>
                                <span>Costo: <strong class="text-slate-700 font-mono">${{ number_format($item->costo, 2) }}</strong></span>
                            @else
                                <span class="text-slate-300">•</span>
                                <span>Costo: <span class="badge bg-slate-100 text-slate-500 border border-slate-200" style="font-size: 0.7rem; font-weight: 500;">N.A.</span></span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if($itemProxima)
                        <span class="text-xs text-slate-500 font-mono bg-slate-50 border border-slate-100 px-2 py-0.5 rounded">
                            Sig: {{ $itemProxima->format('d/m/Y') }}
                        </span>
                        @endif

                        <a href="{{ route('productos.fijos.generarMantenimiento', ['id' => $productoFijo->id, 'mantenimientoId' => $item->id]) }}" 
                           class="text-slate-400 hover:text-primary p-1 rounded transition-colors text-decoration-none d-inline-flex" 
                           title="Descargar orden de este servicio en Word (.docx)">
                            <iconify-icon icon="lucide:file-text" width="14" height="14"></iconify-icon>
                        </a>

                        @if(esSuperAdmin() || tienePermiso('fijos - modificar'))
                        <form action="{{ route('productos_fijos.mantenimiento.eliminar', $item->id) }}" method="POST" 
                              onsubmit="return confirm('¿Eliminar este registro de mantenimiento?');" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors border-0 bg-transparent" title="Eliminar registro">
                                <iconify-icon icon="lucide:trash-2" width="14" height="14"></iconify-icon>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="text-xs text-slate-600 mb-1">
                    {{ $item->descripcion }}
                </div>

                @if($item->comentarios)
                <div class="mt-2 text-xs text-slate-500 bg-slate-50 rounded px-2.5 py-1.5 border border-slate-100">
                    <span class="text-slate-400 font-medium">Nota:</span> {{ $item->comentarios }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
