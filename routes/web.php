<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TypeController as AdminTypeController;
use App\Http\Controllers\Admin\PlaceController as AdminPlaceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\MapController;


Route::get('/', [MapController::class, 'index'])->name('map');

Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware([\App\Http\Middleware\AdminAuth::class])->group(function(){
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('types', AdminTypeController::class);
        Route::resource('places', AdminPlaceController::class);
        // users resource stub
        Route::resource('users', AdminUserController::class);
    });
});
