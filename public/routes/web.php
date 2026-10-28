<?php

use App\Http\Controllers\AdminsController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', [AdminsController::class, 'TemplateAdmin']);
Route::get('/dashboard', [AdminsController::class, 'Dashboard']);
Route::get('/laporan', [AdminsController::class, 'laporan']);
Route::get('/fotografy', [AdminsController::class, 'fotografy']);



// ROUTE GALERY //
Route::get('/admin/galery', [AdminsController::class, 'galeryindex'])->name('admin.galery');
Route::post('/admin/galery/upload', [AdminsController::class, 'storeGalery'])->name('admin.galery.store');
Route::delete('/admin/galery/delete', [AdminsController::class, 'destroyGalery'])->name('admin.galery.destroy');

// ROUTE PROFILE STUDIO //
Route::get('/admin/profile', [AdminsController::class, 'profileIndex'])->name('admin.profile');
Route::post('/admin/profile/update', [AdminsController::class, 'updateProfile'])->name('admin.profile.update');


// Route::get('/', function () {
//     return view('welcome');
// });
