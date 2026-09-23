<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Inicio
Breadcrumbs::for('web.home', function (BreadcrumbTrail $trail) {
    $trail->push('Inicio', route('web.home'));
});

// ========================== EMPRESAS ==========================

Breadcrumbs::for('empresa.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Empresas Internas', route('empresa.index'));
});

Breadcrumbs::for('empresa.indexExterna', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Empresas Externas', route('empresa.indexExterna'));
});

Breadcrumbs::for('empresa.ver', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('empresa.index');
    $trail->push('Ver Empresa', route('empresa.ver', $id));
});

Breadcrumbs::for('empresa.verExterna', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('empresa.indexExterna');
    $trail->push('Ver Empresa Externa', route('empresa.verExterna', $id));
});

Breadcrumbs::for('empresa.ubicaciones', function (BreadcrumbTrail $trail) {
    $trail->parent('empresa.index');
    $trail->push('Ubicaciones', route('empresa.ubicaciones'));
});

Breadcrumbs::for('empresa.ubicaciones.ver', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('empresa.ubicaciones');
    $trail->push('Ver Ubicación', route('empresa.ubicaciones.ver', $id));
});

// ========================== USUARIOS ==========================

Breadcrumbs::for('usuarios.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Usuarios', route('usuarios.index'));
});

Breadcrumbs::for('usuarios.ver', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('usuarios.index');
    $trail->push('Ver Usuario', route('usuarios.ver', $id));
});

Breadcrumbs::for('usuarios.perfil', function (BreadcrumbTrail $trail) {
    $trail->parent('usuarios.index');
    $trail->push('Perfil', route('usuarios.perfil'));
});

// ========================== ROLES ==========================

Breadcrumbs::for('roles.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Roles', route('roles.index'));
});

Breadcrumbs::for('roles.ver', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('roles.index');
    $trail->push('Ver Rol', route('roles.ver', $id));
});

// ========================== ETIQUETAS ==========================

Breadcrumbs::for('etiquetas.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Etiquetas', route('etiquetas.index'));
});

Breadcrumbs::for('etiquetas.ver', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('etiquetas.index');
    $trail->push('Ver Etiqueta', route('etiquetas.ver', $id));
});

// ========================== VEHÍCULOS ==========================

Breadcrumbs::for('vehiculos.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Vehículos', route('vehiculos.index'));
});

Breadcrumbs::for('vehiculos.crear', function (BreadcrumbTrail $trail) {
    $trail->parent('vehiculos.index');
    $trail->push('Crear Vehículo', route('vehiculos.crear'));
});

Breadcrumbs::for('vehiculos.ver', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('vehiculos.index');
    $trail->push('Ver Vehículo', route('vehiculos.ver', $id));
});

// ========================== PRODUCTOS FIJOS ==========================

Breadcrumbs::for('productos.indexFijos', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Productos Fijos', route('productos.indexFijos'));
});

Breadcrumbs::for('productos_fijos.viewFijos', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('productos.indexFijos');
    $trail->push('Ver Producto Fijo', route('productos_fijos.viewFijos', $id));
});

// ========================== PRODUCTOS COMPRA/VENTA ==========================

Breadcrumbs::for('productos_compra_venta.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Productos Compra/Venta', route('productos_compra_venta.index'));
});

Breadcrumbs::for('productos_compra_venta.view', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('productos_compra_venta.index');
    $trail->push('Ver Producto', route('productos_compra_venta.view', $id));
});

// ========================== PRODUCTOS CONSUMIBLES ==========================

Breadcrumbs::for('productos_consumibles.index', function (BreadcrumbTrail $trail) {
    $trail->parent('web.home');
    $trail->push('Productos Consumibles', route('productos_consumibles.index'));
});

Breadcrumbs::for('productos_consumibles.view', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('productos_consumibles.index');
    $trail->push('Ver Consumible', route('productos_consumibles.view', $id));
});

// ========================== PRODUCTOS ==========================

Breadcrumbs::for('productos', function ($trail) {
    $trail->parent('web.home');
    $trail->push('Productos', route('productos'));
});

