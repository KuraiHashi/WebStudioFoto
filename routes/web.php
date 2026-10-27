<?php

use App\Http\Controllers\AdminsController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', [AdminsController::class, 'TemplateAdmin']);
Route::get('/dashboard', [AdminsController::class, 'Dashboard']);


// Route::get('/', function () {
//     return view('welcome');
// });
