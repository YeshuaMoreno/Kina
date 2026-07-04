<?php

use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
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

require __DIR__.'/auth.php';
