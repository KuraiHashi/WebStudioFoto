<?php

use App\Http\Controllers\AdminsController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', [AdminsController::class, 'TemplateAdmin']);
Route::get('/dashboard', [AdminsController::class, 'Dashboard']);
Route::get('/laporan', [AdminsController::class, 'laporan']);
Route::get('/galery', [AdminsController::class, 'galery']);
Route::get('/fotografy', [AdminsController::class, 'fotografy']);
Route::get('/profile', [AdminsController::class, 'profile']);


// Route::get('/', function () {
//     return view('welcome');
// });
