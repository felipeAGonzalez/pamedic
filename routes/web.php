<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetOtherController as Reset;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AttendanceController;



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

Route::group(['middleware' => 'web', 'token.expired'], function () {
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

        Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
        Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
        Route::delete('/patients/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');

        Route::match(['get', 'post'],'password/view', [LoginController::class, 'password'])->name('password.view');
        Route::post('password/reset', [UserController::class, 'changePassword'])->name('password.update');
        Route::post('password/reset/{id}', [UserController::class, 'resetPassword'])->name('password.reset');
    });

    Route::get('/patients/search', [PatientController::class,'search'])->name('patients.search');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::patch('/patients/{id}/photo', [PatientController::class, 'photo'])->name('patients.photo');
    Route::get('/patients/photo/{id}', [PatientController::class, 'showPhoto'])->name('patients.show.photo');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/search', [AttendanceController::class, 'search'])->name('attendance.search');
    Route::patch('/attendance/register/{id}', [AttendanceController::class, 'register'])->name('attendance.register');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/nurseAsigne/{id}', [AttendanceController::class, 'asigne'])->name('attendance.asigne');

    Route::get('/treatment', [TreatmentController::class, 'index'])->name('treatment.index');
    Route::get('/treatment/create/{id}', [TreatmentController::class, 'create'])->name('treatment.create');
    Route::get('/treatment/createPres/{id}', [TreatmentController::class, 'createPres'])->name('treatment.createPres');
    Route::get('/treatment/create/pre-hemo/{id}', [TreatmentController::class, 'createPreHemo'])->name('treatment.createPreHemo');
    Route::get('/treatment/create/trans-hemo/{id}', [TreatmentController::class, 'createTransHemo'])->name('treatment.createTransHemo');
    Route::get('/treatment/create/post-hemo/{id}', [TreatmentController::class, 'createPostHemo'])->name('treatment.createPostHemo');
    Route::get('/treatment/create/evaluation/{id}', [TreatmentController::class, 'createEvaluation'])->name('treatment.createEvaluation');

    Route::post('/treatment/fill', [TreatmentController::class, 'fill'])->name('treatment.fill');
    Route::post('/treatment/fillPres', [TreatmentController::class, 'fillPres'])->name('treatment.fillPres');
    Route::post('/treatment/fill/pre-hemo', [TreatmentController::class, 'fillPreHemo'])->name('treatment.fillPreHemo');
    Route::post('/treatment/fill/trans-hemo', [TreatmentController::class, 'fillTransHemo'])->name('treatment.fillTransHemo');
    Route::post('/treatment/fill/post-hemo', [TreatmentController::class, 'fillPostHemo'])->name('treatment.fillPostHemo');
    Route::post('/treatment/fill/evaluation', [TreatmentController::class, 'fillEvaluation'])->name('treatment.fillEvaluation');

    Route::get('/treatment/{id}', [TreatmentController::class, 'show'])->name('treatment.show');
});
