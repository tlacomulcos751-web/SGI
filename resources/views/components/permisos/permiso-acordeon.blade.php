<div class="accordion-item border mb-3 shadow-sm rounded-3 overflow-hidden">
    <h2 class="accordion-header" id="heading{{ $id }}">
        <button class="accordion-button bg-{{ $color }} bg-opacity-10 fw-bold text-{{ $color }}" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse{{ $id }}">
            <i class="bi bi-{{ $icon }} me-2"></i>{{ $titulo }}
        </button>
    </h2>
    <div id="collapse{{ $id }}" class="accordion-collapse collapse" data-bs-parent="#mainAccordion">
        <div class="accordion-body p-0 bg-white">
            <div class="accordion" id="{{ strtolower($id) }}SubAccordion">
                @foreach ($grupos as $grupo => $perms)
                @php $grupoId = Str::slug($grupo); @endphp
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="heading{{ $id }}{{ $grupoId }}">
                        <button class="accordion-button collapsed py-2 bg-light" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $id }}{{ $grupoId }}">
                            <i class="bi bi-{{ $tituloGrupoIcono }} me-2 text-{{ $color }}" style="margin-left: 20px;"></i>{{ ucfirst($grupo) }}
                        </button>
                    </h2>
                    <div id="collapse{{ $id }}{{ $grupoId }}" class="accordion-collapse collapse"
                        data-bs-parent="#{{ strtolower($id) }}SubAccordion">
                        <div class="accordion-body p-0">
                            <div class="d-flex justify-content-end gap-2 p-3">
                                <button type="button" class="btn btn-sm btn-outline-success seleccionar-todo" data-target="#collapse{{ $id }}{{ $grupoId }}">
                                    Seleccionar todo
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger deseleccionar-todo" data-target="#collapse{{ $id }}{{ $grupoId }}">
                                    Deseleccionar todo
                                </button>
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach ($perms as $permiso)
                                @include('components.permisos.item-permiso', [
                                'permiso' => $permiso,
                                'label' => $labelCallback($permiso),
                                ])
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>