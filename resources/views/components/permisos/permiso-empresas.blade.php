<div class="accordion-item border shadow-sm rounded-3 overflow-hidden">
    <h2 class="accordion-header" id="headingEmpresas">
        <button class="accordion-button bg-info bg-opacity-10 fw-bold text-info" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapseEmpresas">
            <i class="bi bi-buildings me-2"></i>Permisos de Empresas y Ubicaciones
        </button>
    </h2>
    <div id="collapseEmpresas" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
        <div class="accordion-body p-0 bg-white">
            <div class="accordion" id="empresasSubAccordion">
                @foreach ($empresas as $empresa => $perms)
                @php $empresaId = \Illuminate\Support\Str::slug($empresa); @endphp
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingEmp{{ $empresaId }}">
                        <button class="accordion-button collapsed py-2 bg-light" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseEmp{{ $empresaId }}">
                            <i class="bi bi-building me-2 text-info" style="margin-left: 20px;"></i>{{ $empresa }}
                        </button>
                    </h2>
                    <div id="collapseEmp{{ $empresaId }}" class="accordion-collapse collapse"
                        data-bs-parent="#empresasSubAccordion">
                        <div class="accordion-body p-0">
                            <div class="d-flex justify-content-end gap-2 p-3">
                                <button type="button" class="btn btn-sm btn-outline-success seleccionar-todo" data-target="#collapseEmp{{ $empresaId }}">
                                    Seleccionar todo
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger deseleccionar-todo" data-target="#collapseEmp{{ $empresaId }}">
                                    Deseleccionar todo
                                </button>
                            </div>
                            {{-- Permisos de la Empresa --}}
                            <ul class="list-group list-group-flush mb-2">
                                @foreach ($perms as $permiso)
                                @include('components.permisos.item-permiso', [
                                'permiso' => $permiso,
                                'label' => cleanAction($permiso->permiso_nombre),
                                'marginLeft' => '40px'
                                ])
                                @endforeach
                            </ul>

                            {{-- Ubicaciones (si existen para esta empresa) --}}
                            @if ($ubicaciones->has($empresa))
                            <div class="accordion" id="ubicacionesSubAccordion{{ $empresaId }}">
                                @foreach ($ubicaciones[$empresa]->groupBy(fn($p) => explode(' - ', $p->permiso_nombre)[0]) as $ubicacion => $ubicacionPerms)
                                @php $ubicacionId = \Illuminate\Support\Str::slug($ubicacion); @endphp
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header" id="headingUbic{{ $empresaId . $ubicacionId }}">
                                        <button class="accordion-button collapsed py-2 bg-light bg-opacity-50" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseUbic{{ $empresaId . $ubicacionId }}">
                                            <i class="bi bi-geo-alt me-2 text-warning" style="margin-left: 60px;"></i>{{ $ubicacion }}
                                        </button>
                                    </h2>
                                    <div id="collapseUbic{{ $empresaId . $ubicacionId }}" class="accordion-collapse collapse"
                                        data-bs-parent="#ubicacionesSubAccordion{{ $empresaId }}">
                                        <div class="accordion-body p-0">
                                            <div class="d-flex justify-content-end gap-2 p-3">
                                                <button type="button" class="btn btn-sm btn-outline-success seleccionar-todo" data-target="#collapseUbic{{ $empresaId . $ubicacionId }}">
                                                    Seleccionar todo
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger deseleccionar-todo" data-target="#collapseUbic{{ $empresaId . $ubicacionId }}">
                                                    Deseleccionar todo
                                                </button>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                @foreach ($ubicacionPerms as $permiso)
                                                @include('components.permisos.item-permiso', [
                                                'permiso' => $permiso,
                                                'label' => cleanAction($permiso->permiso_nombre),
                                                'marginLeft' => '80px'
                                                ])
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>