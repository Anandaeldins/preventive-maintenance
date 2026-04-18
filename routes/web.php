<?php

use App\Http\Controllers\SegmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FiturController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InspeksiController;
use App\Http\Controllers\MaintenanceTaskController;
use App\Http\Controllers\Teknisi\FmeaController;
use App\Http\Controllers\Teknisi\DashboardController as TeknisiDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KepalaRo\KepalaRoController;
use App\Http\Controllers\Pusat\DashboardController as PusatDashboardController;

/*
|--------------------------------------------------------------------------
| ADMIN USERS (RESOURCE)
|--------------------------------------------------------------------------
| Prefix: /admin
| Name  : admin.*
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class);
    });


/*
|--------------------------------------------------------------------------
| DASHBOARD DEFAULT (/)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');


/*
|--------------------------------------------------------------------------
| SEGMENT (GLOBAL RESOURCE)
|--------------------------------------------------------------------------
| ⚠️ Ada juga resource segments di bagian bawah (duplikat dengan role)
*/
Route::middleware(['auth', 'role:teknisi'])->group(function () {
    Route::resource('segments', SegmentController::class);
});
/*
|--------------------------------------------------------------------------
| AUTH (LOGIN / LOGOUT)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', function () {

    Auth::logout(); // 🔥 WAJIB

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');

})->name('logout');


/*
|--------------------------------------------------------------------------
| FMEA DEMO (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/
Route::match(['get','post'], '/fmea-demo', [FmeaController::class, 'index'])
    ->middleware('auth');

Route::get('/hasilfmea/{id?}', [FmeaController::class, 'hasil'])
    ->middleware('auth')
    ->name('hasilfmea');


/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');
});


/*
|--------------------------------------------------------------------------
| FNEA (STATIC VIEW)
|--------------------------------------------------------------------------
*/
Route::match(['get','post'], '/fnea', function () {
    return view('fnea');
});


/*
|--------------------------------------------------------------------------
| ADMIN FITUR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/admin-fitur', [FiturController::class, 'index'])
        ->name('admin.admin-fitur');
});


/*
|--------------------------------------------------------------------------
| SETTINGS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings', [App\Http\Controllers\SettingsController::class, 'store'])
        ->name('settings.store');

    Route::put('/settings', [App\Http\Controllers\SettingsController::class, 'update'])
        ->name('settings.update');
});


/*
|--------------------------------------------------------------------------
| ACCOUNT (SEMUA AUTH USER)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])
        ->name('account.index');

    Route::put('/account', [App\Http\Controllers\AccountController::class, 'update'])
        ->name('account.update');
});


/*
|--------------------------------------------------------------------------
| INSPEKSI CORE
|--------------------------------------------------------------------------
*/
Route::post('/inspeksi/store', [InspeksiController::class,'store'])
    ->name('inspeksi.store');

Route::post('/inspeksi/submit/{id}', [InspeksiController::class,'submitForApproval'])
    ->name('inspeksi.submit');

Route::get('/inspeksi/risk-summary', [InspeksiController::class,'riskSummary'])
    ->name('inspeksi.risk-summary');


/*
|--------------------------------------------------------------------------
| INSPEKSI - MY REPORTS (ROLE: teknisi,admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teknisi,admin'])->group(function () {
    Route::get('/inspeksi/my-reports', [InspeksiController::class, 'myReports'])
        ->name('inspeksi.my-reports');
});


/*
|--------------------------------------------------------------------------
| PM SCHEDULES (CORE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teknisi'])->group(function () {

    // LIST
    Route::get('/pm-schedules', [App\Http\Controllers\PmScheduleController::class, 'index'])
        ->name('pm-schedules.index');

    // CREATE (role: teknisi,admin)
    Route::get('/pm-schedules/create', [App\Http\Controllers\PmScheduleController::class, 'create'])
        ->name('pm-schedules.create')
        ->middleware('role:teknisi,admin');

    Route::post('/pm-schedules', [App\Http\Controllers\PmScheduleController::class, 'store'])
        ->name('pm-schedules.store')
        ->middleware('role:teknisi,admin');

    // CUSTOM: risk summary (API)
    Route::get('/pm-schedules/risk-summary', [App\Http\Controllers\PmScheduleController::class, 'getRiskSummary'])
        ->name('pm-schedules.risk-summary');

    // SUBMIT APPROVAL
    Route::post('/pm-schedules/{id}/submit', [App\Http\Controllers\PmScheduleController::class, 'submitForApproval'])
        ->name('pm-schedules.submit');

    // CRUD DETAIL
    Route::get('/pm-schedules/{id}', [App\Http\Controllers\PmScheduleController::class, 'show'])
        ->name('pm-schedules.show');

    Route::get('/pm-schedules/{id}/edit', [App\Http\Controllers\PmScheduleController::class, 'edit'])
        ->name('pm-schedules.edit');

    Route::put('/pm-schedules/{id}', [App\Http\Controllers\PmScheduleController::class, 'update'])
        ->name('pm-schedules.update');

    Route::delete('/pm-schedules/{id}', [App\Http\Controllers\PmScheduleController::class, 'destroy'])
        ->name('pm-schedules.destroy');
});




/*
|--------------------------------------------------------------------------
| APPROVAL SYSTEM (PREFIX: /approval)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('approval')
    ->name('approval.')
    ->group(function () {

    // DASHBOARD
    Route::get('/dashboard',
        [App\Http\Controllers\PmScheduleController::class, 'approvalDashboard']
    )->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | KEPALA RO + ADMIN
    |----------------------------------------------------------------------
    */
    Route::middleware('role:kepala_ro,admin')->group(function () {

        Route::get('/schedules',
            [App\Http\Controllers\PmScheduleController::class, 'pendingSchedules']
        )->name('pending.schedules');

        Route::post('/approve/{id}',
            [App\Http\Controllers\PmScheduleController::class, 'approveByRo']
        )->name('approve');

        Route::post('/reject/{id}',
            [App\Http\Controllers\PmScheduleController::class, 'rejectSchedule']
        )->name('reject');

        Route::post('/approve-group',
            [App\Http\Controllers\PmScheduleController::class, 'approveGroup']
        )->name('approve-group');

        Route::post('/reject-group',
            [App\Http\Controllers\PmScheduleController::class, 'rejectGroup']
        )->name('reject-group');
    });

    // HISTORY
    Route::get('/history',
        [App\Http\Controllers\PmScheduleController::class, 'approvalHistory']
    )->name('history');

    Route::get('/rejected',
        [App\Http\Controllers\PmScheduleController::class, 'rejectedSchedules']
    )->name('rejected');

    Route::get('/reports',
        [App\Http\Controllers\PmScheduleController::class, 'pendingReports']
    )->name('pending.reports');
});




/*
|--------------------------------------------------------------------------
| TASK DETAIL
|--------------------------------------------------------------------------
*/
Route::get('/task/{schedule}', [MaintenanceTaskController::class, 'show'])
    ->name('tasks.show');

Route::post('/task/{schedule}', [InspeksiController::class, 'store'])
    ->name('tasks.store');


/*
|--------------------------------------------------------------------------
| REPORT APPROVAL (INSPEKSI)
|--------------------------------------------------------------------------
*/
Route::post('/approval/report/{id}/approve-ro', [InspeksiController::class,'approveByRo'])
    ->name('reports.approve.ro');

Route::post('/approval/report/{id}/reject-ro', [InspeksiController::class,'rejectByRo'])
    ->name('reports.reject.ro');

Route::post('/approval/report/{id}/approve-pusat', [InspeksiController::class,'approveByPusat'])
    ->name('reports.approve.pusat');

Route::post('/approval/report/{id}/reject-pusat', [InspeksiController::class,'rejectByPusat'])
    ->name('reports.reject.pusat');


/*
|--------------------------------------------------------------------------
| REPORT LIST
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:kepala_ro'])->group(function () {
Route::get('/approval/ro-reports', [InspeksiController::class,'pendingRO'])
    ->name('approval.ro.reports');
});

Route::middleware(['auth', 'role:pusat'])->group(function () {
Route::get('/approval/pusat-reports', [InspeksiController::class,'pendingPusat'])
    ->name('approval.pusat.reports');
});

/*
|--------------------------------------------------------------------------
| MAINTENANCE INFO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teknisi,kepala_ro'])->group(function () {
    Route::get('/maintenance/info', [MaintenanceTaskController::class,'info'])
        ->name('maintenance.info');
});

/*
|--------------------------------------------------------------------------
| MODAL REPORT
|--------------------------------------------------------------------------
*/
Route::get('/report/modal/{id}', [InspeksiController::class,'modal'])
    ->name('report.modal');


/*
|--------------------------------------------------------------------------
| TEKNISI DASHBOARD
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teknisi'])->group(function () {
    Route::get('/teknisi/dashboard', [TeknisiDashboardController::class, 'index'])
        ->name('teknisi.dashboard');
});


/*
|--------------------------------------------------------------------------
| FMEA OUTPUT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,kepala_ro,teknisi'])->group(function () {
    Route::get('/fmeaoutput', [FmeaController::class, 'index'])
        ->name('fmea.page');

    Route::get('/fmeaoutput/data', [FmeaController::class, 'output'])
        ->name('fmea.data');
});

/*
|--------------------------------------------------------------------------
| TEKNISI TASKS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teknisi'])->group(function () {

    

    // TASKS (duplikat)
    Route::get('/tasks', [MaintenanceTaskController::class, 'index'])
        ->name('tasks.index');

    Route::get('/task/{schedule}', [MaintenanceTaskController::class, 'show'])
        ->name('tasks.show');

    Route::post('/task/{schedule}', [InspeksiController::class, 'store'])
        ->name('tasks.store');
   
});


/*
|--------------------------------------------------------------------------
| Dashboard KEPALA RO + PUSAT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:kepala_ro'])->group(function () {
    Route::get('/kepalaro/dashboard', function () {
        return view('kepalaro.dashboard');
    })->name('kepalaro.dashboard');
});

Route::middleware(['auth', 'role:pusat'])->group(function () {
    Route::get('/pusat/dashboard', [PusatDashboardController::class, 'dashboard'])
        ->name('pusat.dashboard');
});

Route::get('/kepalaro/dashboard', [KepalaRoController::class, 'dashboard'])
    ->middleware(['auth', 'role:kepala_ro'])
    ->name('kepalaro.dashboard');

    Route::get('/fmea/output', [FmeaController::class, 'output']);