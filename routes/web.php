<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\{
    ConsumibleController,
    EmpresaController,
    EtiquetasController,
    PermisosController,
    ProductoController,
    ResponsivaController,
    WebController,
    ProductoCompraVentaController,
    RolController,
    UsuarioController,
    VehiculosController,
    ExportExcelController,
    ImportExcelController,
    PlantillasController,
};
use Illuminate\Support\Facades\Mail;

// Autenticación
Route::get('/', [WebController::class, 'index'])->name('login');
Route::post('/iniciar-sesion', [WebController::class, 'verificarLogin'])->name('login.post');
Route::get('/home', [WebController::class, 'home'])->name('web.home');
Route::post('/logout', function () {
    Session::flush();
     return redirect()->route('login');
})->name('logout');

// Empresas
Route::controller(EmpresaController::class)->group(function () {
    Route::prefix('empresa')->group(function () {
        Route::post('/insertar', 'insertar')->name('empresa.insertar');
        Route::put('/actualizar', 'actualizar')->name('empresa.actualizar');
        Route::get('/view/{id}', 'ver')->name('empresa.ver');
        Route::get('/viewExterna/{id}', 'ver')->name('empresa.verExterna');
        Route::post('/eliminar-imagenes', 'eliminarImagenesEmpresa')->name('empresa.eliminar.imagenes');

        Route::prefix('ubicaciones')->group(function () {
            Route::get('/', 'indexUbicaciones')->name('empresa.ubicaciones');
            Route::post('/insertar', 'insertarUbicacion')->name('empresa.ubicaciones.insertar');
            Route::get('/{id}', 'verUbicacion')->name('empresa.ubicaciones.ver');
            Route::put('/{id}/actualizar', 'actualizarUbicacion')->name('empresa.ubicaciones.actualizar');
        });
    });

    Route::get('/empresa-interna', 'index')->name('empresa.index');
    Route::get('/empresa-externa', 'indexExterna')->name('empresa.indexExterna');
});

//Roles
Route::controller(RolController::class)->prefix('roles')->group(function () {
    Route::get('/', 'index')->name('roles.index');
    Route::post('/crear', 'insertar')->name('roles.insertar');
    Route::get('/{id}/ver', 'ver')->name('roles.ver');
    Route::put('/{id}', 'actualizar')->name('roles.actualizar');
    Route::put('/{id}/eliminar', 'eliminar')->name('roles.eliminar');
});

// Permisos
Route::controller(PermisosController::class)->prefix('permisos')->group(function () {
    Route::post('/actualizar', 'actualizarPermisos')->name('permisos.actualizar');
});

//usuarios
Route::controller(UsuarioController::class)->prefix('usuarios')->group(function () {
    Route::get('/', 'index')->name('usuarios.index');
    Route::post('/crear', 'insertar')->name('usuarios.insertar');
    Route::get('/{id}/ver', 'ver')->name('usuarios.ver');
    Route::put('/{id}', 'actualizar')->name('usuarios.actualizar');
    Route::put('/{id}/eliminar', 'eliminar')->name('usuarios.eliminar');
    Route::get('/{id}/cambiar-password', 'cambiarPassword')->name('usuarios.cambiarPassword');
    Route::post('/{id}/cambiar-password', 'actualizarPassword')->name('usuarios.actualizarPassword');
    Route::get('/perfil', 'perfil')->name('usuarios.perfil');
    Route::put('/perfil/actualizar', 'actualizarPerfil')->name('usuarios.actualizarPerfil');
});

// Etiquetas
Route::controller(EtiquetasController::class)->prefix('etiquetas')->group(function () {
    Route::get('/', 'index')->name('etiquetas.index');
    Route::post('/', 'insertar')->name('etiquetas.insertar');
    Route::match(['put', 'post', 'delete'], '/desactivar', 'eliminar')->name('etiquetas.desactivar');
    Route::match(['put', 'post', 'delete'], '/{id}/eliminar', 'eliminar')->name('etiquetas.eliminar');
    Route::put('/{id}', 'actualizar')->name('etiquetas.actualizar');
    Route::get('/{id}/ver', 'ver')->name('etiquetas.ver');
    Route::post('/agregar', [ProductoController::class, 'agregarEtiqueta'])->name('etiquetas.agregar');
    Route::post('/eliminar', [ProductoController::class, 'eliminarEtiqueta'])->name('etiquetas.eliminar_producto');
});

//Vehiculos
Route::controller(VehiculosController::class)->prefix('vehiculos')->group(function () {
    Route::get('/', 'index')->name('vehiculos.index');
    Route::get('/crear', 'crear')->name('vehiculos.crear');
    Route::post('/crear', 'insertar')->name('vehiculos.insertar');
    Route::get('/{id}/ver', 'ver')->name('vehiculos.ver');
    Route::get('/{id}/generar-vale', 'generarValeSalida')->name('vehiculos.generarVale');
    Route::put('/{id}', 'actualizar')->name('vehiculos.actualizar');
    Route::put('/{id}/eliminar', 'eliminar')->name('vehiculos.eliminar');
    Route::post('/documentacion/crear/{id}', 'insertarDocumentacion')->name('vehiculos.documentacion.insertar');
    Route::post('/mantenimiento/crear/{id}', 'insertarMantenimiento')->name('vehiculos.mantenimiento.insertar');
});

// Activos Fijos
Route::controller(ProductoController::class)->group(function () {
    Route::get('/catalogo-productos/imprimir', 'imprimirPorResponsable')->name('productos.imprimir_por_responsable');
    Route::get('/catalogo-productos', 'index')->name('productos');
    Route::get('/productosfijos', 'indexFijos')->name('productos.indexFijos');
    Route::post('/productos-fijos/insertar', 'insertar')->name('productos_fijos.insertar');
    Route::get('/productos-fijos/{id}', 'viewFijos')->name('productos_fijos.viewFijos');
    Route::get('/productos-fijos/{id}/qr', 'verQr')->name('productos-fijos.qr');
    Route::get('/productos-fijos/{id}/generar-vale', 'generarValeSalida')->name('productos.fijos.generarVale');

    Route::post('/productos/eliminar-imagenes', 'eliminarImagenesProducto')->name('producto.eliminar.imagenes');
    Route::post('/producto-fijo/subir-imagenes', 'subirImagenesProducto')->name('productoFijo.subirImagenes');

    Route::put('/productos-fijos/{id}/actualizar-info', 'actualizarInfo')->name('productos_fijos.actualizarInfo');
    Route::put('/productos-fijos/{id}/actualizar-ubicacion', 'actualizarUbicacion')->name('productos_fijos.actualizarUbicacion');
    Route::put('/productos-fijos/{id}/actualizar-responsable', 'actualizarResponsable')->name('productos_fijos.actualizarResponsable');
});

// Cartas Responsivas y Carta Poder
Route::controller(ResponsivaController::class)->prefix('responsivas')->group(function () {
    Route::get('/fijo/{id}', 'generarFijo')->name('responsivas.fijo');
    Route::get('/vehiculo/{id}', 'generarVehiculo')->name('responsivas.vehiculo');
    Route::get('/responsable/{id}', 'generarPorResponsable')->name('responsivas.responsable');
});

// Productos de Compra/Venta
Route::controller(ProductoCompraVentaController::class)
    ->prefix('productos-compra-venta')
    ->group(function () {
        Route::get('/', 'index')->name('productos_compra_venta.index');
        Route::get('/{id}', 'view')->name('productos_compra_venta.view');
        Route::post('/insertar', 'insertar')->name('productos_compra_venta.insertar');

        // Rutas nuevas para actualización
        Route::put('/{id}/info', 'actualizarInfo')->name('productos_compra_venta.actualizarInfo');
        Route::put('/{id}/ubicacion', 'actualizarUbicacion')->name('productos_compra_venta.actualizarUbicacion');
        Route::put('/{id}/estado', 'actualizarEstado')->name('productos_compra_venta.actualizarEstado');
    });

// Productos Consumibles
Route::controller(ConsumibleController::class)
    ->prefix('productos-consumibles')
    ->group(function () {
        Route::get('/', 'index')->name('productos_consumibles.index');
        Route::get('/{id}', 'view')->name('productos_consumibles.view');
        Route::post('/insertar', 'insertar')->name('productos_consumibles.insertar');

        // Rutas nuevas para actualización
        Route::put('/{id}/info', 'actualizarInfo')->name('productos_consumibles.actualizarInfo');
        Route::put('/{id}/ubicacion', 'actualizarUbicacion')->name('productos_consumibles.actualizarUbicacion');
        Route::post('/{id}/existencia', 'ajustarExistencia')->name('consumibles.ajustar');
    });

Route::post('/notificaciones_consumibles/eliminar/{id}', [ConsumibleController::class, 'destroy'])
    ->name('notificaciones_consumibles.destroy');
Route::post('/notificaciones_consumibles/crear/', [ConsumibleController::class, 'store'])
    ->name('notificaciones_consumibles.store');

Route::controller(ExportExcelController::class)->group(function () {
    Route::post('/imprimirCompraVenta', 'imprimirCompraVenta')->name('imprimirCompraVenta');
    Route::post('/imprimirFijos', 'imprimirFijos')->name('imprimirFijos');
    Route::post('/imprimirFijosResponsable', 'imprimirFijosResponsable')->name('imprimirFijosResponsable');
    Route::post('/imprimirGeneralResponsable', 'imprimirGeneralResponsable')->name('imprimirGeneralResponsable');
    Route::post('/imprimirConsumibles', 'imprimirConsumibles')->name('imprimirConsumibles');
    Route::post('/imprimirVehiculos', 'imprimirVehiculos')->name('imprimirVehiculos');
    Route::post('/imprimirVehiculosResponsable', 'imprimirVehiculosResponsable')->name('imprimirVehiculosResponsable');
});

// Rutas para importación de datos desde Excel
Route::post('/importar-compra-venta', [ImportExcelController::class, 'importarCompraVenta'])->name('importar.compra.venta');
Route::post('/importar-fijos', [ImportExcelController::class, 'importarFijos'])->name('importar.fijos');
Route::post('/importar-consumibles', [ImportExcelController::class, 'importarConsumibles'])->name('importar.consumibles');
Route::post('/importar-vehiculos', [ImportExcelController::class, 'importarVehiculos'])->name('importar.vehiculos');
Route::get('/plantillas/fijos', [PlantillasController::class, 'descargarFijos'])->name('plantilla.fijos');
Route::get('/plantillas/vehiculos', [PlantillasController::class, 'descargarVehiculos'])->name('plantilla.vehiculos');
Route::get('/plantillas/consumibles', [PlantillasController::class, 'descargarConsumibles'])->name('plantilla.consumibles');
Route::get('/plantillas/compraventa', [PlantillasController::class, 'descargarCompraVenta'])->name('plantilla.compraventa');

// RUTA TEMPORAL PARA HASHEAR CONTRASEÑAS EN PLANO
Route::get('/hashear-passwords-magia', function () {
    $usuarios = \App\Models\Usuario::all();
    $modificados = 0;
    
    foreach ($usuarios as $usuario) {
        // Bcrypt hashes always start with $2y$ (or $2a$, $2b$). 
        // If the password doesn't start with it, it's plain text.
        if (!str_starts_with($usuario->password, '$2y$')) {
            $usuario->password = \Illuminate\Support\Facades\Hash::make($usuario->password);
            $usuario->save();
            $modificados++;
        }
    }
    
    return "¡Magia terminada! Se hashearon $modificados contraseñas que estaban en texto plano.";
});

// RUTA TEMPORAL - ELIMINAR DESPUÉS DE USARLA
Route::get('/clear-cache', function () {
    $key = request('key');
    if ($key !== 'sgi-limpia-2024') {
        abort(403, 'Clave incorrecta');
    }

    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');

    return 'Cache limpiado correctamente. ELIMINA ESTA RUTA AHORA.';
});


// API REST — Endpoints JSON para consumo asíncrono 

use App\Http\Controllers\Api\ApiController;

Route::prefix('api')->middleware('api.session')->controller(ApiController::class)->group(function () {
    // Dashboard
    Route::get('/dashboard', 'dashboard')->name('api.dashboard');

    // Catálogo general unificado
    Route::get('/productos', 'productos')->name('api.productos');

    // Productos Fijos
    Route::get('/productos/fijos', 'productosFijos')->name('api.productos.fijos');
    Route::get('/productos/fijos/{id}', 'productoFijo')->name('api.productos.fijos.show');

    // Productos Consumibles
    Route::get('/productos/consumibles', 'productosConsumibles')->name('api.productos.consumibles');
    Route::get('/productos/consumibles/{id}', 'productoConsumible')->name('api.productos.consumibles.show');

    // Productos Compra/Venta
    Route::get('/productos/compra-venta', 'productosCompraVenta')->name('api.productos.compraventa');
    Route::get('/productos/compra-venta/{id}', 'productoCompraVenta')->name('api.productos.compraventa.show');

    // Vehículos
    Route::get('/vehiculos', 'vehiculos')->name('api.vehiculos');
    Route::get('/vehiculos/{id}', 'vehiculo')->name('api.vehiculos.show');

    // Empresas
    Route::get('/empresas', 'empresas')->name('api.empresas');

    // Usuarios
    Route::get('/usuarios', 'usuarios')->name('api.usuarios');

    // Etiquetas
    Route::get('/etiquetas', 'etiquetas')->name('api.etiquetas');

    // Movimientos
    Route::get('/movimientos', 'movimientos')->name('api.movimientos');

    // Código de barras y pistola lectora
    Route::get('/buscar-codigo-barra', 'buscarCodigoBarra')->name('api.buscar_codigo_barra');
    Route::get('/generar-codigo-barra', 'generarCodigoBarra')->name('api.generar_codigo_barra');
});