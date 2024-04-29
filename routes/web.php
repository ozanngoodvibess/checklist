<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'index'])->name('login');

Route::get('/', function () {
    return view('layout.home');
});

Route::middleware(['guest'])->group(function () {
    Route::view('/', 'halaman_depan/index');
    Route::get('/sesi', [AuthController::class, 'index'])->name('auth');
    Route::post('/sesi', [AuthController::class, 'login']);
    Route::get('/reg', [AuthController::class, 'create'])->name('registrasi');
    Route::post('/reg', [AuthController::class, 'register']);
    Route::get('/verify/{verify_key}', [AuthController::class, 'verify']);
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('/home', '/user');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin')->middleware('userAkses:admin');
    Route::get('/user', [UserController::class, 'index'])->name('user')->middleware('userAkses:user');
});

