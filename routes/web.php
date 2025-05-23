<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetOtherController as Reset;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\EditController;



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


    Route::group(['middleware' => 'position:ROOT,DIRECTIVE,QUALITY,MANAGER,NEPHROLOGIST'], function () {

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

        Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/medicines/create', [MedicineController::class, 'create'])->name('medicines.create');
        Route::post('/medicines', [MedicineController::class, 'store'])->name('medicines.store');
        Route::get('/medicines/{id}/edit', [MedicineController::class, 'edit'])->name('medicines.edit');
        Route::put('/medicines/{id}', [MedicineController::class, 'update'])->name('medicines.update');
        Route::get('/medicines/{id}', [MedicineController::class, 'show'])->name('medicines.show');
        Route::delete('/medicines/{id}', [MedicineController::class, 'destroy'])->name('medicines.destroy');

        Route::get('/print', [PrintController::class, 'index'])->name('print.index');
        Route::get('/print/search', [PrintController::class,'search'])->name('print.search');
        Route::get('/print/expedient/{id}', [PrintController::class,'printNurseExpedient'])->name('print.printNurseExpedient');
        Route::get('/print/expedient/{id}/date/{date}', [PrintController::class,'printNurseExpedient'])->name('print.printNurseExpedientDate');

        Route::get('/print/medic/note', [PrintController::class, 'indexMedicNote'])->name('print.medicNote');
        Route::get('/print/medic/form/{id}/{date}', [PrintController::class, 'createMedicNote'])->name('print.note');
        Route::get('/print/search/note', [PrintController::class,'searchMedicNote'])->name('noteMedic.search');
        Route::post('/print/medic/store', [PrintController::class, 'store'])->name('noteMedic.store');
        Route::get('/print/note/{id}', [PrintController::class,'printMedicNote'])->name('print.printMedicNote');
        Route::get('/print/note/{id}/date/{date}', [PrintController::class,'printMedicNote'])->name('print.noteMedicDate');


        Route::delete('/delete/treatment/{id}', [EditController::class, 'destroyTreatment'])->name('delete.treatment');
    });
    Route::get('/edit', [EditController::class, 'index'])->name('edit.index');
    Route::get('/edit/search', [EditController::class,'search'])->name('edit.search');
    Route::get('/edit/create/{id}/fecha/{date}', [EditController::class, 'create'])->name('edit.create');
    Route::get('/edit/createPres/{id}/fecha/{date}', [EditController::class, 'createPres'])->name('edit.createPres');
    Route::get('/edit/create/pre-hemo/{id}/fecha/{date}', [EditController::class, 'createPreHemo'])->name('edit.createPreHemo');
    Route::get('/edit/create/trans-hemo/{id}/fecha/{date}', [EditController::class, 'createTransHemo'])->name('edit.createTransHemo');
    Route::get('/edit/create/post-hemo/{id}/fecha/{date}', [EditController::class, 'createPostHemo'])->name('edit.createPostHemo');
    Route::get('/edit/create/evaluation/{id}/fecha/{date}', [EditController::class, 'createEvaluation'])->name('edit.createEvaluation');
    Route::get('/edit/create/evaluationN/{id}/fecha/{date}', [EditController::class, 'createEvaluationNurse'])->name('edit.createEvaluationNurse');
    Route::get('/edit/create/medicine/{id}/fecha/{date}', [EditController::class, 'createMedicineAdmin'])->name('edit.createMedicineAdmin');
    Route::get('/edit/finalice/{id}/fecha/{date}', [EditController::class, 'finaliceTreatment'])->name('edit.finaliceTreatment');
    Route::delete('/edit/fill/{id}', [EditController::class, 'destroy'])->name('edit.destroy');
    Route::delete('/edit/fill/{id}', [EditController::class, 'destroyTreatmentPast'])->name('edit.destroyTreatmentPast');

    Route::post('/edit/fill', [EditController::class, 'fill'])->name('edit.fill');
    Route::post('/edit/fillPres', [EditController::class, 'fillPres'])->name('edit.fillPres');
    Route::post('/edit/fill/pre-hemo', [EditController::class, 'fillPreHemo'])->name('edit.fillPreHemo');
    Route::post('/edit/fill/trans-hemo', [EditController::class, 'fillTransHemo'])->name('edit.fillTransHemo');
    Route::post('/edit/fill/post-hemo', [EditController::class, 'fillPostHemo'])->name('edit.fillPostHemo');
    Route::post('/edit/fill/evaluation', [EditController::class, 'fillEvaluation'])->name('edit.fillEvaluation');
    Route::post('/edit/fill/evaluationN', [EditController::class, 'fillNurseEvaluation'])->name('edit.fillNurseEvaluation');
    Route::post('/edit/fill/medicine', [EditController::class, 'fillMedicineAdmin'])->name('edit.fillMedicineAdmin');

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
    Route::get('/treatment/create/evaluationN/{id}', [TreatmentController::class, 'createEvaluationNurse'])->name('treatment.createEvaluationNurse');
    Route::get('/treatment/create/medicine/{id}', [TreatmentController::class, 'createMedicineAdmin'])->name('treatment.createMedicineAdmin');
    Route::get('/treatment/create/oxygen/{id}', [TreatmentController::class, 'createOxygenTherapy'])->name('treatment.createOxygen');
    Route::get('/treatment/create/weight/{id}', [TreatmentController::class, 'createWeight'])->name('treatment.createWeight');
    Route::get('/treatment/finalice/{id}', [TreatmentController::class, 'finaliceTreatment'])->name('treatment.finaliceTreatment');
    Route::delete('/treatment/fill/{id}', [TreatmentController::class, 'destroy'])->name('treatment.destroy');

    Route::post('/treatment/fill', [TreatmentController::class, 'fill'])->name('treatment.fill');
    Route::post('/treatment/fillPres', [TreatmentController::class, 'fillPres'])->name('treatment.fillPres');
    Route::post('/treatment/fill/pre-hemo', [TreatmentController::class, 'fillPreHemo'])->name('treatment.fillPreHemo');
    Route::post('/treatment/fill/trans-hemo', [TreatmentController::class, 'fillTransHemo'])->name('treatment.fillTransHemo');
    Route::post('/treatment/fill/post-hemo', [TreatmentController::class, 'fillPostHemo'])->name('treatment.fillPostHemo');
    Route::post('/treatment/fill/evaluation', [TreatmentController::class, 'fillEvaluation'])->name('treatment.fillEvaluation');
    Route::post('/treatment/fill/evaluationN', [TreatmentController::class, 'fillNurseEvaluation'])->name('treatment.fillNurseEvaluation');
    Route::post('/treatment/fill/medicine', [TreatmentController::class, 'fillMedicineAdmin'])->name('treatment.fillMedicineAdmin');
    Route::post('/treatment/fillWeight', [TreatmentController::class, 'fillWeight'])->name('treatment.fillWeight');
    Route::post('/treatment/fill/oxygen', [TreatmentController::class, 'fillOxygenTherapy'])->name('treatment.fillOxygen');

    Route::match(['get', 'post'],'password/view', [LoginController::class, 'password'])->name('password.view');
    Route::post('password/reset', [UserController::class, 'changePassword'])->name('password.update');
    Route::post('password/reset/{id}', [UserController::class, 'resetPassword'])->name('password.reset');
});
