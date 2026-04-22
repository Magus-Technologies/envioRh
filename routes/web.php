<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Admin\ProcesamientoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de lotes
    Route::resource('lotes', LoteController::class);
    Route::post('lotes/{id}/generar-archivo', [LoteController::class, 'generarArchivo'])->name('lotes.generar-archivo');
    Route::get('lotes/{id}/descargar', [LoteController::class, 'descargarArchivo'])->name('lotes.descargar');
    Route::post('lotes/{id}/enviar-sunat', [LoteController::class, 'enviarASunat'])->name('lotes.enviar-sunat');

    // Descarga PDF procesado (cliente o admin)
    Route::get('recibos/{id}/pdf', [ProcesamientoController::class, 'descargarPdf'])->name('recibos.pdf');

    // Rutas de recibos
    Route::get('lotes/{lote_id}/recibos/create', [ReciboController::class, 'create'])->name('recibos.create');
    Route::post('lotes/{lote_id}/recibos', [ReciboController::class, 'store'])->name('recibos.store');
    Route::get('recibos/{id}/edit', [ReciboController::class, 'edit'])->name('recibos.edit');
    Route::put('recibos/{id}', [ReciboController::class, 'update'])->name('recibos.update');
    Route::delete('recibos/{id}', [ReciboController::class, 'destroy'])->name('recibos.destroy');
    Route::post('recibos/{id}/emitido', [ReciboController::class, 'marcarEmitido'])->name('recibos.emitido');
    Route::post('recibos/calcular-retencion', [ReciboController::class, 'calcularRetencion'])->name('recibos.calcular-retencion');

    // Rutas de clientes
    Route::resource('clientes', ClienteController::class);

    // Rutas de importación
    Route::get('lotes/{lote_id}/importar', [ImportacionController::class, 'create'])->name('importacion.create');
    Route::post('lotes/{lote_id}/importar', [ImportacionController::class, 'store'])->name('importacion.store');
    Route::get('importar/plantilla', [ImportacionController::class, 'descargarPlantilla'])->name('importacion.plantilla');
    Route::get('lotes/{lote_id}/importacion/historial', [ImportacionController::class, 'historial'])->name('importacion.historial');

    // Rutas de reportes
    Route::get('reportes/plame', [ReporteController::class, 'plame'])->name('reportes.plame');
    Route::get('reportes/retenciones', [ReporteController::class, 'retenciones'])->name('reportes.retenciones');
    Route::get('reportes/resumen-lotes', [ReporteController::class, 'resumenLotes'])->name('reportes.resumen-lotes');
    Route::get('reportes/exportar', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar');

    // Panel admin (ingeniero)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('procesamiento', [ProcesamientoController::class, 'index'])->name('procesamiento.index');
        Route::post('procesamiento/{id}/procesar', [ProcesamientoController::class, 'procesar'])->name('procesamiento.procesar');
    });
});

require __DIR__.'/auth.php';
