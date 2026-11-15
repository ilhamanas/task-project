<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Livewire\Account\AccountSettings;
use App\Livewire\Dashboard;
use App\Livewire\Projects\ProjectIndex;
use App\Livewire\Projects\ProjectShow;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/projects', ProjectIndex::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectShow::class)->name('projects.show');
    Route::get('/reports', ReportIndex::class)->name('reports.index');
    Route::get('/account', AccountSettings::class)->name('account.settings');
    Route::get('/users/{user}/avatar', [AccountController::class, 'avatar'])->name('users.avatar');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', UserIndex::class)->name('users.index');
    });
});
