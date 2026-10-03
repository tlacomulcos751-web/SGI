<!-- Modal: Registrar Mantenimiento Preventivo (Diseño Minimalista & Tailwind) -->
<div class="modal fade" id="modalAgregarMantenimientoFijo" tabindex="-1" aria-labelledby="modalAgregarMantenimientoFijoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('productos_fijos.mantenimiento.insertar', $productoFijo->id) }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content border border-slate-200 shadow-2xl rounded-2xl overflow-hidden bg-white">
                
                <!-- Header Minimalista y Formal -->
                <div class="px-6 py-4 border-b border-slate-100 d-flex align-items-center justify-content-between bg-white">
                    <div>
                        <h5 class="modal-title font-semibold text-slate-900 text-base mb-0 tracking-tight" id="modalAgregarMantenimientoFijoLabel">
                            Registrar mantenimiento
                        </h5>
                        <p class="text-xs text-slate-500 mb-0 mt-0.5">
                            {{ $producto->nombre }} <span class="text-slate-300 mx-1">·</span> <span class="font-mono text-slate-600">{{ $productoFijo->clave }}</span>
                        </p>
                    </div>
                    <button type="button" class="btn-close text-slate-400 hover:text-slate-600" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo Limpio del Formulario -->
                <div class="p-6 bg-white">
                    <div class="row g-4">
                        <!-- Fecha del servicio -->
                        <div class="col-md-6">
                            <label for="mantenimiento_fecha" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Fecha de realización <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha" id="mantenimiento_fecha" 
                                   class="form-input-clean w-100" 
                                   value="{{ date('Y-m-d') }}" 
                                   required>
                        </div>

                        <!-- Periodicidad / Frecuencia -->
                        <div class="col-md-6">
                            <label for="frecuencia_meses" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Periodicidad sugerida <span class="text-red-500">*</span>
                            </label>
                            <select name="frecuencia_meses" id="frecuencia_meses" class="form-input-clean w-100" required>
                                <option value="1">Cada 1 mes (Servidores / Alto uso)</option>
                                <option value="3">Cada 3 meses (Trimestral)</option>
                                <option value="6" selected>Cada 6 meses (Semestral)</option>
                                <option value="12">Cada 12 meses (Anual)</option>
                                <option value="24">Cada 24 meses (Bianual)</option>
                            </select>

                            <!-- Próxima fecha calculada discreta e integrada -->
                            <div class="mt-2 d-flex align-items-center gap-1.5 text-xs text-slate-500">
                                <iconify-icon icon="lucide:calendar-clock" width="14" height="14" class="text-slate-400"></iconify-icon>
                                <span>Próximo servicio:</span>
                                <strong id="preview_proxima_fecha" class="text-slate-900 font-mono font-semibold">Calculando...</strong>
                            </div>
                        </div>

                        <!-- Tipo de servicio -->
                        <div class="col-md-6">
                            <label for="tipo_servicio" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Tipo de servicio <span class="text-red-500">*</span>
                            </label>
                            <input type="text" list="tipos_servicios_clean" name="tipo_servicio" id="tipo_servicio" 
                                   class="form-input-clean w-100" 
                                   placeholder="Ej: Mantenimiento preventivo general" 
                                   required maxlength="150">
                            <datalist id="tipos_servicios_clean">
                                <option value="Mantenimiento preventivo general">
                                <option value="Limpieza física interna y soplado">
                                <option value="Cambio de pasta térmica">
                                <option value="Optimización de software y antivirus">
                                <option value="Formateo y actualización de SO">
                                <option value="Revisión de batería y periféricos">
                            </datalist>
                        </div>

                        <!-- Técnico o Proveedor -->
                        <div class="col-md-6">
                            <label for="tecnico" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Técnico / Responsable
                            </label>
                            <input type="text" name="tecnico" id="tecnico" 
                                   class="form-input-clean w-100" 
                                   value="{{ Session::has('usuario') ? Session::get('usuario')->nombre : 'Depto. de Sistemas' }}" 
                                   placeholder="Nombre o empresa técnica" 
                                   maxlength="255">
                        </div>

                        <!-- Costo con opción N/A -->
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                <label for="costo" class="block text-xs font-medium text-slate-700 mb-0">
                                    Costo / Precio ($)
                                </label>
                                <div class="form-check form-switch m-0 d-flex align-items-center gap-1.5">
                                    <input class="form-check-input" type="checkbox" role="switch" id="no_aplica_costo_mnt" 
                                           onchange="toggleNoAplicaCostoMnt(this, 'costo')" 
                                           style="cursor: pointer; width: 2.1em; height: 1.05em;">
                                    <label class="form-check-label text-xs font-semibold text-slate-500 user-select-none" 
                                           for="no_aplica_costo_mnt" style="cursor: pointer; font-size: 0.72rem;">
                                        No aplica (N.A.)
                                    </label>
                                </div>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-slate-50 border border-slate-200 text-slate-500 text-sm font-semibold rounded-start-lg" id="addon-costo-mnt">$</span>
                                <input type="number" step="0.01" min="0" name="costo" id="costo" 
                                       class="form-control form-input-clean rounded-0" 
                                       placeholder="0.00" style="min-width: 0;">
                                <button type="button" class="btn btn-outline-secondary border border-slate-200 text-xs font-semibold d-flex align-items-center gap-1 px-2.5 rounded-end-lg" 
                                        id="btn-na-costo-action" onclick="toggleNoAplicaBoton('no_aplica_costo_mnt', 'costo')" title="Marcar como No Aplica">
                                    <iconify-icon icon="lucide:ban" width="13" height="13"></iconify-icon>
                                    <span>N/A</span>
                                </button>
                            </div>
                            <div id="hint-na-costo-mnt" class="form-text text-muted text-xs mt-1 d-none" style="font-size: 0.72rem;">
                                <iconify-icon icon="lucide:info" width="12" height="12" class="text-primary me-0.5"></iconify-icon>
                                <span>Este servicio se registrará sin costo / servicio interno (N.A.).</span>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-7">
                            <label for="comentarios" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Observaciones / Recomendaciones <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" name="comentarios" id="comentarios" 
                                   class="form-input-clean w-100" 
                                   placeholder="Ej: Equipo en óptimas condiciones, se sugiere cambio de SSD en 1 año" 
                                   maxlength="1000">
                        </div>

                        <!-- Actividades realizadas -->
                        <div class="col-12">
                            <label for="descripcion" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Detalle de actividades realizadas <span class="text-red-500">*</span>
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="3" 
                                      class="form-input-clean w-100" 
                                      placeholder="Describe brevemente las acciones realizadas (limpieza, lubricación, sustitución de insumos, etc.)..." 
                                      required maxlength="2000"></textarea>
                        </div>
                    </div>

                    <!-- Nota técnica al pie muy sutil -->
                    <div class="mt-4 pt-3 border-t border-slate-100 d-flex align-items-center gap-1.5 text-xs text-slate-400">
                        <iconify-icon icon="lucide:info" width="14" height="14" class="text-slate-400 flex-shrink-0"></iconify-icon>
                        <span>Para registrar fallas correctivas o equipos fuera de servicio, edita el estado a <em>"En Reparación"</em>.</span>
                    </div>
                </div>

                <!-- Footer Sobrio y Formal -->
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 d-flex justify-content-end align-items-center gap-2">
                    <button type="button" class="btn-clean-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-clean-primary">
                        <iconify-icon icon="lucide:check" width="15" height="15"></iconify-icon>
                        <span>Guardar registro</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Estilos Scoped inspirados en Tailwind UI / Shadcn */
.form-input-clean {
    border: 1px solid #e2e8f0;
    background-color: #ffffff;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: #0f172a;
    transition: all 0.15s ease-in-out;
}
.form-input-clean:focus {
    outline: none;
    border-color: #0f172a;
    box-shadow: 0 0 0 1px #0f172a;
}
.form-input-clean::placeholder {
    color: #94a3b8;
}
.btn-clean-secondary {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s ease;
}
.btn-clean-secondary:hover {
    background-color: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1;
}
.btn-clean-primary {
    background-color: #0f172a;
    border: 1px solid #0f172a;
    color: #ffffff;
    padding: 0.45rem 1.15rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.btn-clean-primary:hover {
    background-color: #1e293b;
    border-color: #1e293b;
    color: #ffffff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('mantenimiento_fecha');
    const frecuenciaSelect = document.getElementById('frecuencia_meses');
    const previewSpan = document.getElementById('preview_proxima_fecha');

    function calcularProximaFecha() {
        if (!fechaInput || !frecuenciaSelect || !previewSpan) return;
        const fechaVal = fechaInput.value;
        const meses = parseInt(frecuenciaSelect.value, 10);

        if (!fechaVal || isNaN(meses)) {
            previewSpan.textContent = 'Fecha inválida';
            return;
        }

        const partes = fechaVal.split('-');
        if (partes.length !== 3) return;

        const anio = parseInt(partes[0], 10);
        const mes = parseInt(partes[1], 10) - 1; // 0-indexed
        const dia = parseInt(partes[2], 10);

        const fechaBase = new Date(anio, mes, dia);
        fechaBase.setMonth(fechaBase.getMonth() + meses);

        const dd = String(fechaBase.getDate()).padStart(2, '0');
        const mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
        const yyyy = fechaBase.getFullYear();

        previewSpan.textContent = `${dd}/${mm}/${yyyy} (${meses} ${meses === 1 ? 'mes' : 'meses'})`;
    }

    if (fechaInput && frecuenciaSelect) {
        fechaInput.addEventListener('change', calcularProximaFecha);
        frecuenciaSelect.addEventListener('change', calcularProximaFecha);
        calcularProximaFecha();
    }
});

function toggleNoAplicaBoton(switchId, inputId) {
    const switchEl = document.getElementById(switchId);
    if (!switchEl) return;
    switchEl.checked = !switchEl.checked;
    toggleNoAplicaCostoMnt(switchEl, inputId);
}

function toggleNoAplicaCostoMnt(checkbox, inputId) {
    const input = document.getElementById(inputId);
    const addon = document.getElementById('addon-costo-mnt');
    const hint = document.getElementById('hint-na-costo-mnt');
    const btn = document.getElementById('btn-na-costo-action');
    if (!input) return;

    if (checkbox.checked) {
        input.dataset.oldValue = input.value;
        input.value = '';
        input.disabled = true;
        input.placeholder = 'N.A. (No aplica)';
        input.classList.add('bg-slate-100', 'text-slate-400');
        if (addon) addon.classList.add('opacity-50');
        if (hint) hint.classList.remove('d-none');
        if (btn) {
            btn.classList.remove('btn-outline-secondary', 'border-slate-200');
            btn.classList.add('btn-dark', 'bg-slate-800', 'text-white');
            btn.innerHTML = '<iconify-icon icon="lucide:check" width="13" height="13"></iconify-icon> <span>N/A</span>';
            btn.title = 'Hacer clic para ingresar precio';
        }
    } else {
        input.disabled = false;
        input.placeholder = '0.00';
        input.value = input.dataset.oldValue || '';
        input.classList.remove('bg-slate-100', 'text-slate-400');
        if (addon) addon.classList.remove('opacity-50');
        if (hint) hint.classList.add('d-none');
        if (btn) {
            btn.classList.remove('btn-dark', 'bg-slate-800', 'text-white');
            btn.classList.add('btn-outline-secondary', 'border-slate-200');
            btn.innerHTML = '<iconify-icon icon="lucide:ban" width="13" height="13"></iconify-icon> <span>N/A</span>';
            btn.title = 'Marcar como No Aplica';
        }
        input.focus();
    }
}
</script>
