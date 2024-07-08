<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetOtherController as Reset;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LoginController::class, 'showLoginForm'])->name('showLoginForm');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'web'], function () {
    Route::get('/', function () {
        if (Auth::check()) {
            return redirect()->route('welcome');
        } else {
            return view('auth.login');
        }
    });
});
Route::group(['middleware'=>['auth']],function () {

    Route::get('/welcome', function () {
                return view('welcome');
            })->name('welcome');


    Route::group(['middleware' => 'position:ROOT,DIRECTIVE'], function () {

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::match(['get', 'post'],'password/view', [LoginController::class, 'password'])->name('password.view');
        Route::post('password/reset', [UserController::class, 'changePassword'])->name('password.update');
        Route::post('password/reset/{id}', [UserController::class, 'resetPassword'])->name('password.reset');
    });
});
