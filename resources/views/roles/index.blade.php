@extends('layouts.navigation')
@section('title', 'Roles')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar - Collapsible on mobile -->
        <div class="col-lg-2 col-md-3 d-md-block sidebar collapse" id="roleSidebar">
            @livewire('role-list')
        </div>

        <!-- Main content area -->
        <div class="col-lg-10 col-md-9 ms-sm-auto px-md-4 py-3">
            <!-- Mobile menu button -->
            <button class="btn btn-primary d-md-none mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#roleSidebar">
                <i class="bi bi-list"></i> Menu Roles
            </button>
            @livewire('role-details')
        </div>
    </div>
</div>

<script>
    console.log('Roles page loaded');

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('seleccionar-todo')) {
            const target = event.target.getAttribute('data-target');
            const container = document.querySelector(target);
            if (container) {
                container.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
            }
        }

        if (event.target.classList.contains('deseleccionar-todo')) {
            const target = event.target.getAttribute('data-target');
            const container = document.querySelector(target);
            if (container) {
                container.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
        }
    });
</script>

@endsection

@include('roles.modales.rolModal')