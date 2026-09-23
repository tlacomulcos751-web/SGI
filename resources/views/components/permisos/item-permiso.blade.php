<li class="list-group-item d-flex justify-content-between align-items-center">
    <span class="fw-medium">
        <i class="bi {{ getPermisoIcon($permiso->permiso_nombre) }} me-2" style="margin-left: {{ $marginLeft ?? '40px' }}"></i>
        {{ $label ?? formatCamelCase($permiso->permiso_nombre) }}
    </span>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch"
            id="permiso-{{ $permiso->id }}"
            name="permisos[]"
            value="{{ $permiso->id }}"
            {{ $permiso->permiso_otorgado === 'activo' ? 'checked' : '' }}>
    </div>
</li>