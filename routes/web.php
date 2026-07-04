<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\ConnectionRequestController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Landing pública (la landing con branding Kina se construye en la Fase 2).
Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'onboarded'])->name('dashboard');

// Onboarding en 7 pasos (requiere sesión, pero NO el gate 'onboarded').
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', fn () => redirect()->route('onboarding.basico'));

    Route::get('datos-basicos', [OnboardingController::class, 'basicoShow'])->name('basico');
    Route::post('datos-basicos', [OnboardingController::class, 'basicoStore'])->name('basico.store');

    Route::get('que-buscas', [OnboardingController::class, 'intencionShow'])->name('intencion');
    Route::post('que-buscas', [OnboardingController::class, 'intencionStore'])->name('intencion.store');

    Route::get('intereses', [OnboardingController::class, 'interesesShow'])->name('intereses');
    Route::post('intereses', [OnboardingController::class, 'interesesStore'])->name('intereses.store');

    Route::get('comunicacion', [OnboardingController::class, 'comunicacionShow'])->name('comunicacion');
    Route::post('comunicacion', [OnboardingController::class, 'comunicacionStore'])->name('comunicacion.store');

    Route::get('etiquetas', [OnboardingController::class, 'etiquetasShow'])->name('etiquetas');
    Route::post('etiquetas', [OnboardingController::class, 'etiquetasStore'])->name('etiquetas.store');

    Route::get('privacidad', [OnboardingController::class, 'privacidadShow'])->name('privacidad');
    Route::post('privacidad', [OnboardingController::class, 'privacidadStore'])->name('privacidad.store');

    Route::get('foto', [OnboardingController::class, 'fotoShow'])->name('foto');
    Route::post('foto', [OnboardingController::class, 'fotoStore'])->name('foto.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Fase 4: descubrimiento, perfiles, solicitudes, bloqueos y reportes.
Route::middleware(['auth', 'onboarded'])->group(function () {
    Route::get('/descubrir', [DiscoverController::class, 'index'])->name('descubrir.index');

    Route::get('/perfiles/{user}', [PublicProfileController::class, 'show'])->name('perfiles.show');
    Route::post('/perfiles/{user}/conectar', [ConnectionRequestController::class, 'store'])->name('perfiles.conectar');
    Route::post('/perfiles/{user}/bloquear', [BlockController::class, 'store'])->name('perfiles.bloquear');
    Route::get('/perfiles/{user}/reportar', [ReportController::class, 'create'])->name('perfiles.reportar');
    Route::post('/perfiles/{user}/reportar', [ReportController::class, 'store'])->name('perfiles.reportar.store');

    Route::get('/solicitudes', [ConnectionRequestController::class, 'index'])->name('solicitudes.index');
    Route::post('/solicitudes/{connectionRequest}/aceptar', [ConnectionRequestController::class, 'accept'])->name('solicitudes.aceptar');
    Route::post('/solicitudes/{connectionRequest}/rechazar', [ConnectionRequestController::class, 'reject'])->name('solicitudes.rechazar');

    // Fase 5: conexiones y chat.
    Route::get('/conexiones', [ConnectionController::class, 'index'])->name('conexiones.index');
    Route::get('/conversaciones/{conversation}', [ConversationController::class, 'show'])->name('conversaciones.show');
    Route::post('/conversaciones/{conversation}/mensajes', [ConversationController::class, 'storeMessage'])->name('conversaciones.mensajes.store');
});

require __DIR__.'/auth.php';
