<?php

use App\Http\Controllers\AdminsController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', [AdminsController::class, 'TemplateAdmin']);
Route::get('/dashboard', [AdminsController::class, 'Dashboard']);
Route::get('/laporan', [AdminsController::class, 'laporan']);
Route::get('/fotografy', [AdminsController::class, 'fotografy']);
Route::get('/profile', [AdminsController::class, 'profile']);



// ROUTE GALERY //
Route::get('/admin/galery', [AdminsController::class, 'galeryindex'])->name('admin.galery');
Route::post('/admin/galery/upload', [AdminsController::class, 'storeGalery'])->name('admin.galery.store');
Route::delete('/admin/galery/delete', [AdminsController::class, 'destroyGalery'])->name('admin.galery.destroy');


// Route::get('/', function () {
//     return view('welcome');
// });
