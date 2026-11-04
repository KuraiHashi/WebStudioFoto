<?php

use App\Http\Controllers\AdminsController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\PaketFotografiController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\ReservasiController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// USER VIEW
Route::get('/', [GaleriController::class, 'index']);

// PAKET FOTOGRAFI
Route::get('/paket/{id}', [PaketFotografiController::class, 'show'])->name('paket.detail');

// RESERVASI
Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi.index');
Route::post('/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
// Halaman Cek Status Reservasi (Sesuai referensi 1)
Route::get('/reservasi/cek', [ReservasiController::class, 'checkStatusForm'])->name('reservasi.cek');
Route::get('/reservasi/result', [ReservasiController::class, 'checkStatus'])->name('reservasi.result');
// CEK JADWAL RESERVASI
Route::get('/api/reservasi/booked-slots', [ReservasiController::class, 'getBookedSchedules'])->name('reservasi.booked-slots');

// Halaman Form Pembayaran / Upload Bukti (Sesuai referensi 2)
Route::get('/reservasi/pembayaran', [ReservasiController::class, 'paymentPage'])->name('reservasi.pembayaran');
Route::post('/reservasi/payment-history', [ReservasiController::class, 'storePaymentHistory'])->name('reservasi.payment.store');

// USER VIEW
    // PAKET FOTOGRAFI
    // Route::get('/paket/{id}', [PaketFotografiController::class, 'show'])->name('paket.detail');

// ROUTE ADMIN

// Route Halaman Dashboard Admin
Route::get('/dashboard', [AdminsController::class, 'Dashboard'])->name('admin.dashboard');

// Route API untuk Dashboard Admin (Tanpa Middleware sesuai permintaan)
Route::get('/admin/api/reservations', [AdminsController::class, 'getReservations'])->name('admin.api.reservations');
Route::get('/admin/api/schedule', [AdminsController::class, 'getSchedule'])->name('admin.api.schedule');

// Route Laporan (Opsional jika tombol laporan di-klik)
Route::get('/admin/laporan', [AdminsController::class, 'laporan'])->name('admin.laporan');

// KELOLA REKENING
Route::get('/rekening', [RekeningController::class, 'index'])->name('rekening.index');
Route::post('/rekening', [RekeningController::class, 'store'])->name('rekening.store');
Route::put('/rekening/{rekening}', [RekeningController::class, 'update'])->name('rekening.update');
Route::delete('/rekening/{rekening}', [RekeningController::class, 'destroy'])->name('rekening.destroy');

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
