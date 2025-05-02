<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PreventaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\BajaController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\PickingController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\TipoVentaController;
use App\Http\Controllers\CobranzasController;
use App\Http\Controllers\DetalleCargoClienteController;
use App\Http\Controllers\AbonosClienteController;
use App\Models\CargosCliente;
use App\Http\Controllers\ContabilidadController;

// Rutas públicas (Acceso sin autenticación)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/buscar-productos', [IngresoController::class, 'buscarProductos']);
Route::get('/buscar-clientes', [ClienteController::class, 'buscarClientes']);
Route::resource('tipos_ventas', TipoVentaController::class);
Route::get('/clientes/buscar-nombre', [ClienteController::class, 'buscarClientesPorNombre'])->name('clientes.buscarNombre');
Route::get('/preventas/generar-pdf-filtros', [PreventaController::class, 'generarPDFConFiltros'])->name('preventas.generarPDFConFiltros');
Route::get('/rutas/generar-pdf', [RutaController::class, 'generarPDFConFiltros'])
    ->name('rutas.generarPDFConFiltros');
Route::get('/clientes/generar-pdf', [ClienteController::class, 'generarPDFConFiltros'])
    ->name('clientes.generarPDFConFiltros');





// Rutas protegidas (Acceso con autenticación)
Route::middleware('auth')->group(function () {

    Route::get('/ingresos/asignados', [IngresoController::class, 'ingresosAsignados'])->name('ingresos.asignados');
    // Dashboard general
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de ventas y su dashboard
    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('sales.dashboard'); // Dashboard de ventas
        Route::get('/ruta', [SaleController::class, 'registrarRuta'])->name('sales.registrar_ruta'); // Registrar ruta
    });

    // Gestión de clientes
    Route::prefix('clientes')->group(function () {
        Route::get('/registrar', [ClienteController::class, 'create'])->name('clientes.registrar');

        Route::get('/', [ClienteController::class, 'index'])->name('clientes.index');
        Route::middleware('role:administrador')->group(function () {
            Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
            Route::put('/{id}', [ClienteController::class, 'update'])->name('clientes.update');
            Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
        });
    });
    // Gestión de preventas
    Route::prefix('preventas')->group(function () {
        // Listar preventas
        Route::get('/', [PreventaController::class, 'index'])->name('preventas.index');
        Route::get('/crear-desde-ruta', [PreventaController::class, 'crearDesdeRuta'])->name('preventas.crearDesdeRuta');

        // Crear preventa
        Route::get('/create', [PreventaController::class, 'create'])->name('preventas.create');

        // Editar preventa
        Route::get('/{id}/edit', [PreventaController::class, 'edit'])->name('preventas.edit');

        // Actualizar preventa
        Route::put('/{id}', [PreventaController::class, 'update'])->name('preventas.update');

        Route::delete('/eliminar-multiples', [PreventaController::class, 'eliminarMultiples'])->name('preventas.eliminarMultiples');




        Route::get('/{preventaId}/detalles-preventa', [PreventaController::class, 'obtenerDetallesPreventa'])->name('preventas.detalles-preventa');
        Route::get('/buscar-productos', [PreventaController::class, 'buscarProductos'])->name('preventas.buscar-productos');
        Route::post('/eliminar-producto/{detalleId}', [PreventaController::class, 'eliminarProductoDePreventa'])
            ->name('preventas.eliminarProducto');
        Route::post('/eliminar-detalle/{detalleId}', [PreventaController::class, 'eliminarDetallePreventa']);

        Route::post('/reponer-stock/{productoId}', [PreventaController::class, 'reponerStock'])->name('preventas.reponerStock');

        // Guardar nueva preventa
        Route::post('/', [PreventaController::class, 'store'])->name('preventas.store');

        // Mostrar detalles de una preventa
        Route::get('/{id}', [PreventaController::class, 'show'])->name('preventas.show');

        // Eliminar preventa
        Route::delete('/{id}', [PreventaController::class, 'destroy'])->name('preventas.destroy');

        // Generar PDF de una preventa
        Route::get('/{id}/pdf', [PreventaController::class, 'generarPDF'])->name('preventas.pdf');

        // Consultar fechas de vencimiento de productos
        Route::get('/ingresos/{codigoProducto}/fechas-vencimiento', [IngresoController::class, 'getFechasVencimiento']);

        // Generar nota de remisión para una preventa
        Route::get('/{id}/nota-remision', [PreventaController::class, 'generarNotaRemision'])
            ->name('preventas.nota-remision')
            ->middleware(['auth']);
    });


    Route::prefix('picking')->group(function () {
        Route::get('/', [PickingController::class, 'index'])->name('picking.index');
        Route::post('/preparar/{id}', [PickingController::class, 'preparar'])->name('picking.preparar');
        Route::post('/shipped/{id}', [PickingController::class, 'marcarEntregado'])->name('picking.shipped');
        Route::get('/exportar-pdf/{fecha}', [PickingController::class, 'exportarPDF'])->name('picking.exportarPDF');
        Route::post('/{id}/entregado', [PickingController::class, 'entregado'])->name('picking.entregado');
        Route::post('/{id}/no-entregado', [PickingController::class, 'noEntregado'])->name('picking.noEntregado');
    });


    // Gestión de rutas
    Route::get('/rutas', [RutaController::class, 'index'])->name('rutas.index');
    Route::get('/rutas/futuras', [RutaController::class, 'futuras'])->name('rutas.futuras');
    Route::get('/rutas/{id}', [RutaController::class, 'show'])->name('rutas.show');
    Route::post('/rutas/registrar-visita/{clienteId}', [RutaController::class, 'registrarVisita'])->name('rutas.registrarVisita');
    // Gestión de inventario
    Route::prefix('inventario')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventario.index');
        Route::post('/', [InventoryController::class, 'store'])->name('inventario.store');
        Route::get('/{id}', [InventoryController::class, 'show'])->name('inventario.show');
        Route::put('/{id}', [InventoryController::class, 'update'])->name('inventario.update');
        Route::delete('/{id}', [InventoryController::class, 'destroy'])->name('inventario.destroy');
    });

    // Gestión de almacenes, ingresos y bajas (Requiere rol de administrador)
    Route::middleware('admin')->group(function () {
        // Almacenes
        Route::post('/almacenes', [AlmacenController::class, 'store'])->name('almacenes.store');
        Route::delete('/almacenes/{id}', [AlmacenController::class, 'destroy'])->name('almacenes.destroy');

        // Ingresos
        Route::prefix('ingresos')->group(function () {
            Route::get('/', [IngresoController::class, 'index'])->name('ingresos.index');
            Route::post('/', [IngresoController::class, 'store'])->name('ingresos.store');
            Route::post('/check-duplicate', [IngresoController::class, 'checkDuplicate'])->name('ingresos.checkDuplicate');
            Route::post('/get-product', [IngresoController::class, 'getProduct'])->name('ingresos.getProduct');
            Route::delete('/{id}', [IngresoController::class, 'destroy'])->name('ingresos.destroy');
            Route::put('/{id}', [IngresoController::class, 'update'])->name('ingresos.update');
        });

        // Bajas
        Route::prefix('bajas')->group(function () {
            Route::get('/', [BajaController::class, 'index'])->name('bajas.index');
            Route::get('/buscar', [BajaController::class, 'buscar'])->name('bajas.buscar');
            Route::post('/registrar', [BajaController::class, 'registrarBaja'])->name('bajas.registrar');
        });

        // Usuarios
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        });
        Route::prefix('clientes')->group(function () {
            // Index: visible para todos los usuarios autenticados
            Route::get('/', [ClienteController::class, 'index'])->name('clientes.index')->middleware('auth');

            // Crear y guardar clientes: restringido por rol
            Route::middleware('admin')->group(function () {
                Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
                Route::put('/{id}', [ClienteController::class, 'update'])->name('clientes.update');
                Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
            });
        });

        Route::get('/rutas', [RutaController::class, 'index'])->name('rutas.index');
    });
    Route::prefix('reportes')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reportes.index')->middleware('auth');
        Route::get('/reportes/ventas', [ReportesController::class, 'ventas'])->name('reportes.ventas');
        Route::get('/ventas/pdf', [ReportesController::class, 'pdfVentas'])->name('reportes.ventas.pdf');
        Route::get('/reportes/ventas/detallado', [ReportesController::class, 'ventasDetallado'])->name('reportes.ventas.detallado');
        Route::get('/reportes/ventas/general', [ReportesController::class, 'ventasGeneral'])->name('reportes.ventas.general');
        Route::get('/reportes/ventas/general/pdf', [ReportesController::class, 'ventasGeneralPdf'])->name('reportes.ventas.general.pdf');



        // Rutas para visualizar los reportes con filtros
        Route::get('/ingresos', [ReportesController::class, 'vistaIngresos'])->name('reportes.ingresos');
        Route::get('/clientes', [ReportesController::class, 'vistaClientes'])->name('reportes.clientes');
        Route::get('/rutas', [ReportesController::class, 'vistaRutas'])->name('reportes.rutas');

        Route::get('/preventas', [ReportesController::class, 'vistaPreventas'])->name('reportes.preventas');
        Route::get('/rutas/por-preventista/{id}', [RutaController::class, 'getRutasPorPreventista']);


        // Rutas para generar los reportes en PDF
        Route::get('/ingresos/pdf', [ReportesController::class, 'pdfIngresos'])->name('reportes.ingresos.pdf');
        Route::get('/clientes/pdf', [ReportesController::class, 'pdfClientes'])->name('reportes.clientes.pdf');
        Route::get('/rutas/pdf', [ReportesController::class, 'pdfRutas'])->name('reportes.rutas.pdf');
        Route::get('/preventas/pdf', [ReportesController::class, 'pdfPreventas'])->name('reportes.preventas.pdf');
    });

    Route::prefix('contabilidad')->group(function () {
        Route::get('/crear-credito', [CobranzasController::class, 'crearCredito'])->name('contabilidad.cobranzas.crearCredito');
        Route::get('/cuenta-externa/crear', [CobranzasController::class, 'crearCuentaExterna'])->name('contabilidad.cobranzas.crearExterna');
        Route::post('/cuenta-externa/guardar', [CobranzasController::class, 'guardarCuentaExterna'])->name('contabilidad.cobranzas.guardarExterna');
        Route::get('/cuentas-externas', [CobranzasController::class, 'verCuentasExternas'])->name('contabilidad.cobranzas.externas.index');
        Route::get('/cuentas-externas/historial/{id}', [CobranzasController::class, 'verHistorialCuentaExterna'])->name('contabilidad.cobranzas.externas.historial');
        Route::get('/cuentas-externas/pdf/listado', [CobranzasController::class, 'generarPDFListadoCuentasExternas'])->name('contabilidad.cobranzas.externas.generarPDF');
        Route::get('/cuentas-externas/pdf/historial/{id}', [CobranzasController::class, 'generarPDFHistorialCuentaExterna'])->name('contabilidad.cobranzas.externas.historialPDF');



        // 📌 Dashboard de Contabilidad
        Route::get('/', [ContabilidadController::class, 'index'])->name('contabilidad.index');

        // 📌 Módulo de Cobranzas dentro de Contabilidad
        Route::prefix('cobranzas')->group(function () {

            // ✅ 📌 Dashboard de cobranzas
            Route::get('/', [CobranzasController::class, 'index'])->name('contabilidad.cobranzas.index');

            // ✅ 📌 Registro de una cuenta por cobrar (crédito)
            Route::get('/registrar', [CobranzasController::class, 'crearCredito'])->name('contabilidad.cobranzas.crear');
            Route::post('/registrar', [CobranzasController::class, 'storeCredito'])->name('contabilidad.cobranzas.storeCredito');

            // ✅ 📌 Registro de un nuevo abono (pago del cliente)
            Route::get('/abono', [CobranzasController::class, 'crearAbono'])->name('contabilidad.cobranzas.abono');
            Route::post('/abono', [CobranzasController::class, 'storeAbono'])->name('contabilidad.cobranzas.registrarAbono');

            // ✅ 📌 Historial de pagos de un cliente
            Route::get('/historial/{cliente_id}', [CobranzasController::class, 'historialPagos'])
                ->name('contabilidad.cobranzas.historial');

            // ✅ 📌 Generación de recibo en PDF (mostrar en nueva pestaña)
            Route::get('/recibo/ver/{abono_id}', [CobranzasController::class, 'generarRecibo'])
                ->name('contabilidad.cobranzas.generarRecibo');

            // ✅ 📌 Descargar recibo en PDF
            Route::get('/descargar-recibo/{abono_id}', [CobranzasController::class, 'descargarRecibo'])
                ->name('contabilidad.cobranzas.descargarRecibo');

            // ✅ 📌 Buscar clientes para filtro de cobranzas
            Route::get('/buscar-clientes', [CobranzasController::class, 'buscarClientes'])
                ->name('cobranzas.buscarClientes');

            // ✅ 📌 Nota de remisión de una preventa (si aplica)
            Route::get('/nota-remision/{id}', [CobranzasController::class, 'generarNotaRemision'])
                ->name('contabilidad.cobranzas.nota-remision');

            // ✅ 📌 Generar PDF de cobranzas con filtros aplicados
            Route::get('/generar-pdf', [CobranzasController::class, 'generarPDFConFiltros'])
                ->name('contabilidad.cobranzas.generarPDFConFiltros');

            // ✅ 📌 Generar reporte de cobranzas general en PDF
            Route::get('/pdf', [CobranzasController::class, 'generarPDFCobranzas'])
                ->name('contabilidad.cobranzas.generarPDF');


            // Puedes agregar más rutas si quieres:
            Route::get('/estado-cuentas', [CobranzasController::class, 'estadoCuentas'])->name('contabilidad.cobranzas.estadoCuentas');
            Route::post('/guardar-abono', [CobranzasController::class, 'storeAbono'])->name('contabilidad.cobranzas.storeAbono');

            Route::get('/api/cliente/{id}/creditos', function ($id) {
                return \App\Models\CargosCliente::where('cliente_id', $id)
                    ->where('saldo_pendiente', '>', 0)
                    ->get(['id', 'numero_credito', 'saldo_pendiente']);
            });
            Route::get('/abono', [CobranzasController::class, 'crearAbono'])->name('contabilidad.cobranzas.abono');
            Route::get('/api/cliente/{id}/creditos', function ($id) {
                return CargosCliente::where('cliente_id', $id)
                    ->where('saldo_pendiente', '>', 0)
                    ->get(['id', 'numero_credito', 'saldo_pendiente']);
            });
            Route::get('/registrar', [CobranzasController::class, 'crearCredito'])->name('contabilidad.cobranzas.registrarCredito');
            Route::post('/guardar', [CobranzasController::class, 'guardarCreditoManual'])->name('contabilidad.cobranzas.guardarCredito');

            Route::get('/buscar-todos-clientes', [CobranzasController::class, 'buscarTodosLosClientes'])
                ->name('cobranzas.buscarTodos');
        });
    });
});
Route::get('/api/cliente/{id}/creditos', function ($id) {
    return CargosCliente::where('cliente_id', $id)
        ->where('saldo_pendiente', '>', 0)
        ->get(['id', 'numero_credito', 'saldo_pendiente']);
});
