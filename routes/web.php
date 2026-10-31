<?php

use App\Http\Controllers\AdminsController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\PaketFotografiController;
use App\Http\Controllers\ReservasiController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', [GaleriController::class, 'index']);
Route::get('/dashboard', [AdminsController::class, 'Dashboard']);
Route::get('/laporan', [AdminsController::class, 'laporan']);


// USER VIEW
    // PAKET FOTOGRAFI
    Route::get('/paket/{id}', [PaketFotografiController::class, 'show'])->name('paket.detail');
    
    // RESERVASI
    Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi.index');
    Route::post('/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');



// ROUTE GALERY //
Route::get('/admin/galery', [AdminsController::class, 'galeryindex'])->name('admin.galery');
Route::post('/admin/galery/upload', [AdminsController::class, 'storeGalery'])->name('admin.galery.store');
Route::delete('/admin/galery/delete', [AdminsController::class, 'destroyGalery'])->name('admin.galery.destroy');

// ROUTE PROFILE STUDIO //
Route::get('/admin/profile', [AdminsController::class, 'profileIndex'])->name('admin.profile');
Route::post('/admin/profile/update', [AdminsController::class, 'updateProfile'])->name('admin.profile.update');

// ROUTE FOTOGRAPHY
Route::get('/fotografi', [AdminsController::class, 'fotografy'])->name('admin.fotografi');
Route::post('/fotografi', [AdminsController::class, 'storeFotografy'])->name('admin.fotografi.store');
Route::put('/fotografi/{id}', [AdminsController::class, 'updateFotografy'])->name('admin.fotografi.update');
Route::delete('/fotografi/{id}', [AdminsController::class, 'destroyFotografy'])->name('admin.fotografi.destroy');


// Route::get('/', function () {
//     return view('welcome');
// });
