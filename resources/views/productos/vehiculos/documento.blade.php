<div class="row g-3">
    <!-- Tipo -->
    <div class="col-md-3">
        <label class="form-label fw-semibold" style="color: #322a5a;">
            <i class="bi bi-file-earmark-text me-1" style="color: #cc5536;"></i>Tipo
        </label>
        <input type="text" name="documentos[{{ $index }}][tipo]" class="form-control shadow-sm"
            style="border-color: #6277b7; border-radius: 0.5rem;" placeholder="Ej. Licencia, Tarjeta de Circulación">
    </div>

    <!-- Número de Documento -->
    <div class="col-md-3">
        <label class="form-label fw-semibold" style="color: #322a5a;">
            <i class="bi bi-123 me-1" style="color: #cc5536;"></i>Número de Documento
        </label>
        <input type="text" name="documentos[{{ $index }}][numero_documento]" class="form-control shadow-sm"
            style="border-color: #6277b7; border-radius: 0.5rem;" placeholder="Ej. 1234567890">
    </div>

    <!-- Vigencia Inicio -->
    <div class="col-md-3">
        <label class="form-label fw-semibold" style="color: #322a5a;">
            <i class="bi bi-calendar-date me-1" style="color: #cc5536;"></i>Vigencia Inicio
        </label>
        <input type="date" name="documentos[{{ $index }}][vigencia_inicio]" class="form-control shadow-sm"
            style="border-color: #6277b7; border-radius: 0.5rem;">
    </div>

    <!-- Vigencia Fin -->
    <div class="col-md-3">
        <label class="form-label fw-semibold" style="color: #322a5a;">
            <i class="bi bi-calendar-check me-1" style="color: #cc5536;"></i>Vigencia Fin
        </label>
        <input type="date" name="documentos[{{ $index }}][vigencia_fin]" class="form-control shadow-sm"
            style="border-color: #6277b7; border-radius: 0.5rem;">
    </div>

    <!-- Estatus -->
    <div class="col-md-3">
        <label class="form-label fw-semibold" style="color: #322a5a;">
            <i class="bi bi-check-circle me-1" style="color: #cc5536;"></i>Estatus
        </label>
        <select name="documentos[{{ $index }}][estatus]" class="form-select shadow-sm"
            style="border-color: #6277b7; border-radius: 0.5rem;">
            <option value="">Seleccionar...</option>
            <option value="vigente">Vigente</option>
            <option value="vencido">Vencido</option>
            <option value="proceso">En proceso</option>
        </select>
    </div>
</div>