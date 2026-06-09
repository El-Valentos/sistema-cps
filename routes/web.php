<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BeneficiarioController;
use App\Http\Controllers\OrdenPagoController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ContabilidadController;
use App\Http\Controllers\ArchivosController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\FinancieraController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ConsultaChequeController;

// ==================== RUTAS PÚBLICAS ====================

Route::get('/', function () {
    return redirect()->route('login');
});

// Consulta pública de cheques para clientes
Route::get('/consulta-cheque', [ConsultaChequeController::class, 'index'])->name('consulta-cheque.index');
Route::post('/consulta-cheque', [ConsultaChequeController::class, 'buscar'])->name('consulta-cheque.buscar');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// ==================== RUTAS PROTEGIDAS ====================

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== BENEFICIARIOS ====================
    Route::middleware(['role:Tesorería|Super Admin'])->group(function () {
        Route::resource('beneficiarios', BeneficiarioController::class);
    });
    // API para búsqueda de beneficiarios (usado en formularios) - accesible por usuarios autenticados
    Route::get('/api/v1/beneficiarios/buscar', [BeneficiarioController::class, 'buscar'])->name('api.beneficiarios.buscar');

    // ==================== ÓRDENES DE PAGO ====================
    Route::middleware(['role:Tesorería|Financiera|Contabilidad|Presupuesto|Administración|Caja|Archivos|Super Admin'])->prefix('ordenes-pago')->name('ordenes-pago.')->group(function () {
        Route::get('/', [OrdenPagoController::class, 'index'])->name('index');
        Route::get('/create', [OrdenPagoController::class, 'create'])->name('create');
        Route::post('/', [OrdenPagoController::class, 'store'])->name('store');
        Route::get('/{ordenPago}', [OrdenPagoController::class, 'show'])->name('show');
        Route::get('/{ordenPago}/edit', [OrdenPagoController::class, 'edit'])->name('edit');
        Route::put('/{ordenPago}', [OrdenPagoController::class, 'update'])->name('update');
        Route::get('/{ordenPago}/pdf', [OrdenPagoController::class, 'generarPDF'])->name('pdf');
        Route::post('/{ordenPago}/aprobar', [OrdenPagoController::class, 'aprobar'])->name('aprobar');
        Route::post('/{ordenPago}/reenviar-financiera', [OrdenPagoController::class, 'reenviarFinanciera'])->name('reenviar-financiera');
        Route::post('/enviar-masivo', [OrdenPagoController::class, 'enviarMasivo'])->name('enviar-masivo');
        Route::get('/{ordenPago}/generar-cheque', [OrdenPagoController::class, 'generarCheque'])->name('generate-cheque');
    });

    // ==================== FINANCIERA ====================
    Route::middleware(['role:Financiera|Super Admin'])->prefix('financiera')->name('financiera.')->group(function () {
        Route::get('/', [FinancieraController::class, 'index'])->name('index');
        Route::post('/{ordenPago}/aprobar', [FinancieraController::class, 'aprobar'])->name('aprobar');
        Route::post('/{ordenPago}/rechazar', [FinancieraController::class, 'rechazar'])->name('rechazar');
        Route::post('/aprobar-masivo', [FinancieraController::class, 'aprobarMasivo'])->name('aprobarMasivo');
        Route::get('/cheques', [FinancieraController::class, 'verCheques'])->name('cheques');
        Route::post('/cheques/aprobar-masivo', [FinancieraController::class, 'aprobarChequeMasivo'])->name('aprobarChequeMasivo');
        Route::post('/cheques/{cheque}/aprobar', [FinancieraController::class, 'aprobarCheque'])->name('aprobarCheque');
        Route::post('/cheques/{cheque}/rechazar', [FinancieraController::class, 'rechazarCheque'])->name('rechazarCheque');
    });

    // ==================== CHEQUES ====================
    Route::middleware(['role:Contabilidad|Caja|Financiera|Super Admin'])->prefix('cheques')->name('cheques.')->group(function () {
        Route::get('/', [ChequeController::class, 'index'])->name('index');
        Route::get('/buscar', [ChequeController::class, 'buscar'])->name('buscar');
        Route::post('/buscar', [ChequeController::class, 'buscarPost'])->name('buscarPost');
        Route::get('/create', [ChequeController::class, 'create'])->name('create');
        Route::post('/', [ChequeController::class, 'store'])->name('store');
        Route::get('/{cheque}', [ChequeController::class, 'show'])->name('show');
        Route::get('/{cheque}/editar', [ChequeController::class, 'edit'])->name('editar');
        Route::put('/{cheque}', [ChequeController::class, 'update'])->name('update');
        Route::post('/{cheque}/confirmar', [ChequeController::class, 'confirmar'])->name('confirmar');
        Route::post('/enviar-masivo', [ChequeController::class, 'enviarMasivo'])->name('enviar-masivo');
        Route::get('/{cheque}/imprimir', [ChequeController::class, 'imprimir'])->name('imprimir');
        Route::get('/{cheque}/pdf', [ChequeController::class, 'generarPDF'])->name('pdf');
        Route::post('/{cheque}/anular', [ChequeController::class, 'anular'])->name('anular');
    });

    // ==================== CAJA ====================
    Route::middleware(['role:Caja|Super Admin'])->prefix('caja')->name('caja.')->group(function () {
        Route::get('/', [App\Http\Controllers\CajaController::class, 'index'])->name('index');
        Route::get('/{ordenPago}', [App\Http\Controllers\CajaController::class, 'show'])->name('show');
        Route::post('/{ordenPago}/entrega', [App\Http\Controllers\CajaController::class, 'registrarEntrega'])->name('entrega');
        Route::post('/{ordenPago}/enviar-contabilidad', [App\Http\Controllers\CajaController::class, 'enviarContabilidad'])->name('enviarContabilidad');
        Route::post('/enviar-contabilidad-masivo', [App\Http\Controllers\CajaController::class, 'enviarContabilidadMasivo'])->name('enviarContabilidadMasivo');
        Route::post('/{ordenPago}/cobrado', [App\Http\Controllers\CajaController::class, 'marcarCobrado'])->name('cobrado');
        Route::post('/{ordenPago}/revalidar', [App\Http\Controllers\CajaController::class, 'revalidar'])->name('revalidar');
        Route::post('/revalidar-masivo', [App\Http\Controllers\CajaController::class, 'revalidarMasivo'])->name('revalidarMasivo');
    });

    // ==================== CONTABILIDAD ====================
    Route::middleware(['role:Contabilidad|Super Admin'])->prefix('contabilidad')->name('contabilidad.')->group(function () {
        Route::get('/', [ContabilidadController::class, 'index'])->name('index');
        Route::post('/{ordenPago}/aprobar', [ContabilidadController::class, 'aprobar'])->name('aprobar');
        Route::post('/{ordenPago}/rechazar', [ContabilidadController::class, 'rechazar'])->name('rechazar');
        Route::get('/cheques', [ContabilidadController::class, 'verCheques'])->name('cheques');
        Route::post('/cheques/enviar-admin-masivo', [ContabilidadController::class, 'enviarAdministracionMasivo'])->name('enviarAdministracionMasivo');
        Route::get('/cheques/{cheque}', [ContabilidadController::class, 'showCheque'])->name('showCheque');
        Route::post('/cheques/{cheque}/enviar-presupuesto', [ContabilidadController::class, 'enviarPresupuesto'])->name('enviarPresupuesto');
        Route::put('/cheques/{cheque}/editar', [ContabilidadController::class, 'editarCheque'])->name('editarCheque');
        Route::post('/cheques/{cheque}/anular', [ContabilidadController::class, 'anularCheque'])->name('anularCheque');
        Route::post('/cheques/{cheque}/enviar-admin', [ContabilidadController::class, 'enviarAdministracion'])->name('enviarAdministracion');
        Route::post('/aprobar-masivo', [ContabilidadController::class, 'aprobarMasivo'])->name('aprobarMasivo');
        Route::get('/revision-cheques', [ContabilidadController::class, 'revisionCheques'])->name('revision_cheques');
        Route::post('/{ordenPago}/enviar-archivos', [ContabilidadController::class, 'enviarAArchivos'])->name('enviarArchivos');
        Route::post('/enviar-archivos-masivo', [ContabilidadController::class, 'enviarAArchivosMasivo'])->name('enviarArchivosMasivo');
    });

    // ==================== ARCHIVOS ====================
    Route::middleware(['role:Archivos|Super Admin'])->prefix('archivos')->name('archivos.')->group(function () {
        Route::get('/', [ArchivosController::class, 'index'])->name('index');
        Route::post('/{ordenPago}/archivar', [ArchivosController::class, 'archivar'])->name('archivar');
        Route::get('/archivados', [ArchivosController::class, 'archivados'])->name('archivados');
    });

    // ==================== PRESUPUESTO ====================
    Route::middleware(['role:Presupuesto|Super Admin'])->prefix('presupuesto')->name('presupuesto.')->group(function () {
        Route::get('/', [PresupuestoController::class, 'index'])->name('index');
        Route::get('/{cheque}', [PresupuestoController::class, 'show'])->name('show');
        Route::post('/{cheque}/aprobar', [PresupuestoController::class, 'aprobar'])->name('aprobar');
        Route::post('/aprobar-masivo', [PresupuestoController::class, 'aprobarMasivo'])->name('aprobarMasivo');
        Route::post('/{cheque}/rechazar', [PresupuestoController::class, 'rechazar'])->name('rechazar');
    });

    // ==================== ADMINISTRACIÓN ====================
    Route::middleware(['role:Administración|Super Admin'])->prefix('administracion')->name('administracion.')->group(function () {
        Route::get('/', [AdministracionController::class, 'index'])->name('index');
        Route::get('/{cheque}', [AdministracionController::class, 'show'])->name('show');
        Route::post('/{cheque}/aprobar', [AdministracionController::class, 'aprobar'])->name('aprobar');
        Route::post('/aprobar-masivo', [AdministracionController::class, 'aprobarMasivo'])->name('aprobarMasivo');
        Route::post('/{cheque}/rechazar', [AdministracionController::class, 'rechazar'])->name('rechazar');
    });

    // ==================== TRACKING ====================
    Route::middleware(['auth'])->prefix('tracking')->name('tracking.')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('index');
        Route::get('/pdf', [TrackingController::class, 'generarPDF'])->name('pdf');
        Route::get('/{ordenPago}', [TrackingController::class, 'show'])->name('show');
        Route::post('/{ordenPago}/actualizar', [TrackingController::class, 'actualizar'])->name('actualizar');
        Route::post('/{ordenPago}/entrega', [TrackingController::class, 'registrarEntrega'])->name('entrega');
        Route::post('/{ordenPago}/cerrar', [TrackingController::class, 'cerrar'])->name('cerrar');
    });

    // ==================== REPORTES ====================
    Route::middleware(['role:Tesorería|Financiera|Contabilidad|Presupuesto|Administración|Caja|Super Admin'])->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::post('/generar', [ReporteController::class, 'generar'])->name('generar');
    });

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ==================== USUARIOS (SUPER ADMIN) ====================
    Route::middleware(['auth', 'role:Super Admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        
        // Rutas adicionales para gestión de usuarios
        Route::post('users/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('users.toggleActivo');
        Route::post('users/{user}/asignar-super-admin', [UserController::class, 'asignarSuperAdmin'])->name('users.asignarSuperAdmin');
        Route::post('users/{user}/quitar-super-admin', [UserController::class, 'quitarSuperAdmin'])->name('users.quitarSuperAdmin');

        // ==================== ÁREAS (SUPER ADMIN) ====================
        Route::resource('areas', AreaController::class)->except(['show', 'destroy']);
    });
});

// ==================== VERIFICACIÓN EMAIL ====================
Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Ruta 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

// Ruta de actualización de contraseña (perfil)
Route::middleware(['auth'])->group(function () {
    Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
});
