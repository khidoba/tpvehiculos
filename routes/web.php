<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vehiculos\RegisterController;
use App\Http\Controllers\Vehiculos\DataTablesController;
use App\Http\Controllers\Vehiculos\CalendarioController;
use App\Http\Controllers\Vehiculos\MaterialController;
use App\Http\Controllers\Vehiculos\AsignacionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAgency;


Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'check.agency'])->group(function () {
    // Usuarios
    //Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware(['auth', 'role:Administrador de Sistema|Administrador de Agencia']);
    Route::resource('users', UserController::class);

    // Vehículos
    Route::get('/registrarVehiculo', [RegisterController::class, 'index'])->name('registrar')->middleware('can:registrarVehiculo');
    Route::post('/registrarVehiculo', [RegisterController::class, 'store'])->name('registrar')->middleware('can:registrarVehiculo');
    Route::get('/consultarVehiculo', [DataTablesController::class, 'index'])->name('consultarVehiculo')->middleware('can:consultarVehiculo');
    Route::get('/vehiculos', [DataTablesController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/{id}/edit', [DataTablesController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{id}', [DataTablesController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{id}', [DataTablesController::class, 'destroy'])->name('vehiculos.destroy');
    Route::get('/vehiculos/{id}/info', [DataTablesController::class, 'getVehiculoInfo']);
    Route::get('/vehiculos/{id}/mantenimiento-report', [DataTablesController::class, 'getMantenimientoReport']);
    Route::get('/get-tecnicos-disponibles-asignados', [DataTablesController::class, 'getTecnicosDisponiblesYAsignados'])->name('get.tecnicos.disponibles.asignados');
    Route::get('/vehiculos/{id}/tecnicos-asignados', [DataTablesController::class, 'getTecnicosAsignados'])->name('vehiculos.tecnicos.asignados');

    // Mantenimientos
    Route::post('/mantenimientos', [DataTablesController::class, 'storeMantenimiento']);
    Route::get('/get-tecnicos-disponibles', [DataTablesController::class, 'getTecnicosDisponibles'])->name('get.tecnicos.disponibles');

    // Calendario
    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index')->middleware('can:verCalendario');
    Route::get('/calendario/eventos', [CalendarioController::class, 'getEventos'])->name('calendario.eventos');
    Route::get('/calendario/materiales', [CalendarioController::class, 'getMateriales'])->name('calendario.materiales');
    Route::post('/calendario/registrar-material', [CalendarioController::class, 'registrarMaterial'])->name('calendario.registrarMaterial');
    Route::post('/calendario/registrar-mantenimiento', [CalendarioController::class, 'registrarMantenimiento'])->name('calendario.registrarMantenimiento')->middleware('auth');
    Route::post('/calendario/aprobar-mantenimiento/{id}', [CalendarioController::class, 'aprobarMantenimiento'])->name('calendario.aprobarMantenimiento')->middleware(['auth', 'role:Administrador de Sistema|Administrador de Agencia']);
    Route::post('/calendario/rechazar-mantenimiento/{id}', [CalendarioController::class, 'rechazarMantenimiento'])
    ->name('calendario.rechazarMantenimiento')
    ->middleware(['auth', 'role:Administrador de Sistema|Administrador de Agencia']);

    Route::get('/asignar-vehiculos', [AsignacionController::class, 'index'])->name('vehiculos.asignar.index')->middleware('can:asignar_vehiculos');
    Route::post('/asignar-vehiculos', [AsignacionController::class, 'asignar'])->name('vehiculos.asignar')->middleware('can:asignar_vehiculos');
    Route::get('/vehiculos-asignados', [AsignacionController::class, 'vehiculosAsignados'])->name('vehiculos.asignados');

    Route::get('/get-vehiculos', [AsignacionController::class, 'getVehiculos'])->name('get.vehiculos');
    Route::get('/get-tecnicos', [AsignacionController::class, 'getTecnicos'])->name('get.tecnicos');
    Route::get('/get-vehiculos-asignados', [AsignacionController::class, 'getVehiculosAsignados'])->name('get.vehiculos.asignados');

    Route::post('/vehiculos/desasignar', [AsignacionController::class, 'desasignar'])->name('vehiculos.desasignar');


});
