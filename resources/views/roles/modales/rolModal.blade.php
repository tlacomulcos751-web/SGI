<!-- Modal de Rol - Versión Mejorada -->
<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formRol" method="POST" class="needs-validation" novalidate>
            <div class="modal-content shadow-lg">
                <!-- Encabezado del Modal -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-5 fw-bold" id="modalRolLabel">
                        <i class="fas fa-user-tag me-2"></i>Insertar Rol
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body py-4">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="metodoForm">
                    <input type="hidden" name="id" id="rolId">

                    <!-- Campo Nombre -->
                    <div class="mb-4">
                        <label for="nombre" class="form-label fw-semibold">
                            <i class="fas fa-tag me-1 text-muted"></i> Nombre del rol
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-edit text-primary"></i>
                            </span>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                maxlength="100" required placeholder="Ej: Administrador">
                            <div class="invalid-feedback">
                                Por favor ingresa un nombre válido para el rol.
                            </div>
                        </div>
                    </div>

                    <!-- Campo Estado -->
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">
                            <i class="fas fa-toggle-on me-1 text-muted"></i> Estado
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="activo" selected>Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <!-- Mensaje de Confirmación para Eliminar -->
                    <div id="mensajeEliminar" class="alert alert-danger d-none mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡Advertencia!</strong> ¿Estás seguro que deseas eliminar este rol?
                        Esta acción no se puede deshacer.
                    </div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnGuardar">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Función para abrir modal en modo creación
    function abrirModalCrear() {
        limpiarFormulario();
        const modal = document.getElementById('modalRol');

        // Configuración UI
        modal.querySelector('.modal-header').className = 'modal-header bg-primary text-white';
        modal.querySelector('#modalRolLabel').innerHTML = '<i class="fas fa-user-tag me-2"></i>Insertar Rol';
        modal.querySelector('#btnGuardar').innerHTML = '<i class="fas fa-save me-1"></i> Guardar';
        modal.querySelector('#btnGuardar').className = 'btn btn-primary rounded-pill px-4';

        // Configuración del formulario
        document.getElementById('formRol').action = '/roles/crear';
        document.getElementById('metodoForm').value = 'POST';

        // Mostrar modal
        new bootstrap.Modal(modal).show();
    }

    // Función para abrir modal en modo edición
    function abrirModalEditar(rol) {
        limpiarFormulario();
        const modal = document.getElementById('modalRol');

        // Configuración UI
        modal.querySelector('.modal-header').className = 'modal-header bg-warning text-dark';
        modal.querySelector('#modalRolLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Rol';
        modal.querySelector('#btnGuardar').innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar';
        modal.querySelector('#btnGuardar').className = 'btn btn-warning rounded-pill px-4';

        // Configuración del formulario
        document.getElementById('formRol').action = `/roles/${rol.id}`;
        document.getElementById('metodoForm').value = 'PUT';
        document.getElementById('nombre').value = rol.nombre;
        document.getElementById('status').value = rol.status;
        document.getElementById('rolId').value = rol.id;

        // Mostrar modal
        new bootstrap.Modal(modal).show();
    }

    // Función para abrir modal en modo eliminación
    function abrirModalEliminar(rol) {
        limpiarFormulario();
        const modal = document.getElementById('modalRol');

        // Configuración UI
        modal.querySelector('.modal-header').className = 'modal-header bg-danger text-white';
        modal.querySelector('#modalRolLabel').innerHTML = '<i class="fas fa-trash-alt me-2"></i>Eliminar Rol';
        modal.querySelector('#btnGuardar').innerHTML = '<i class="fas fa-trash me-1"></i> Eliminar';
        modal.querySelector('#btnGuardar').className = 'btn btn-danger rounded-pill px-4';

        // Configuración del formulario
        document.getElementById('formRol').action = `/roles/${rol.id}/eliminar`;
        document.getElementById('metodoForm').value = 'PUT';
        document.getElementById('rolId').value = rol.id;
        document.getElementById('nombre').value = rol.nombre;
        document.getElementById('status').value = rol.status;

        // Deshabilitar campos y mostrar mensaje
        document.getElementById('nombre').disabled = true;
        document.getElementById('status').disabled = true;
        document.getElementById('mensajeEliminar').classList.remove('d-none');

        // Mostrar modal
        new bootstrap.Modal(modal).show();
    }

    // Función para limpiar el formulario
    function limpiarFormulario() {
        const form = document.getElementById('formRol');
        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('rolId').value = '';
        document.getElementById('nombre').disabled = false;
        document.getElementById('status').disabled = false;
        document.getElementById('mensajeEliminar').classList.add('d-none');
    }

    // Validación del formulario
    (function() {
        'use strict';

        const form = document.getElementById('formRol');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();
</script>