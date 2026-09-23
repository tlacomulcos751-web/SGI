<div class="h-100 p-3 role-sidebar">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold text-primary">
            <i class="bi bi-people-fill me-2"></i>Roles
        </h1>
        <button class="btn btn-sm btn-outline-primary d-md-none" data-bs-toggle="collapse" data-bs-target="#roleSidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="list-group list-group-flush role-selector">
        @foreach ($roles as $role)
        @php
        $isActive = ($role->status ?? $role->estatus) == "activo";
        $isSelected = $selectedRoleId == $role->id;
        $statusClass = $isActive ? 'active-role' : 'inactive-role';
        $selectedClass = $isSelected ? 'selected-role' : '';
        @endphp

        <button
            wire:click="selectRole({{ $role->id }})"
            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 
                   {{ $statusClass }} {{ $selectedClass }}"
            aria-current="{{ $isSelected ? 'true' : 'false' }}">
            <span class="role-name">
                <i class="bi bi-person-badge me-2"></i>
                {{ $role->nombre }}
                @if(!$isActive)
                <small class="ms-2">(Inactivo)</small>
                @endif
            </span>

            <span class="badge rounded-pill user-count">
                {{ $role->users_count() ?? 0 }}
            </span>
        </button>
        @endforeach
    </div>

    <div class="mt-3">
        @if(tienePermiso('insertarRol'))
        <button class="btn btn-success w-100" onclick="abrirModalCrear()">
            <i class="bi bi-plus-circle"></i> Nuevo Rol
        </button>
        @endif
    </div>

    <style>
        /* ===== Light Theme (default) ===== */
        .role-sidebar {
            background-color: #f8f9fa;
        }

        .role-selector .list-group-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            background-color: #f8f9fa;
            margin-bottom: 2px;
        }

        .role-selector .active-role {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .role-selector .inactive-role {
            background-color: #f5f5f5;
            color: #000000;
        }

        .role-selector .selected-role {
            background-color: #bbdefb !important;
            color: #0d47a1 !important;
            border-left: 4px solid #0d47a1;
            font-weight: 500;
        }

        .role-selector .list-group-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .role-selector .user-count {
            background-color: #42a5f5;
            color: white;
            min-width: 2rem;
        }

        .role-selector .inactive-role .user-count {
            background-color: #9e9e9e;
        }

        .role-selector .selected-role .user-count {
            background-color: #0d47a1;
        }

        /* ===== Dark Theme ===== */
        [data-theme="dark"] .role-sidebar {
            background-color: #1a1d21;
        }

        [data-theme="dark"] .role-selector .list-group-item {
            background-color: #2b2f35;
            color: #c9d1d9;
            border-color: transparent;
        }

        [data-theme="dark"] .role-selector .active-role {
            background-color: #1b3a26;
            color: #6fcf7f;
        }

        [data-theme="dark"] .role-selector .inactive-role {
            background-color: #2b2f35;
            color: #ffffff;
        }

        [data-theme="dark"] .role-selector .selected-role {
            background-color: #1a2c4e !important;
            color: #6eaef7 !important;
            border-left: 4px solid #6eaef7;
        }

        [data-theme="dark"] .role-selector .list-group-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            background-color: #363b44;
        }

        [data-theme="dark"] .role-selector .user-count {
            background-color: #2563a8;
        }

        [data-theme="dark"] .role-selector .inactive-role .user-count {
            background-color: #555;
        }

        [data-theme="dark"] .role-selector .selected-role .user-count {
            background-color: #1d4ed8;
        }
    </style>
</div>