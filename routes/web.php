<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Auth;

// =======================
// Default Route - Redirect to Login
// =======================
Route::get('/', function () {
    return redirect('/login');
});

// =======================
// Google Login Routes
// =======================
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

// =======================
// ToDoList Routes
// =======================
Route::middleware('auth')->group(function () {
    // =======================
    // Task Routes
    // =======================
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // =======================
    // Import Routes
    // =======================
    Route::get('/tasks/import', [TaskController::class, 'showImportForm'])->name('tasks.import.form');
    Route::post('/tasks/import', [TaskController::class, 'import'])->name('tasks.import');
    Route::get('/tasks/import/template', [TaskController::class, 'downloadTemplate'])->name('tasks.import.template');

    // =======================
    // Export Routes
    // =======================
    Route::get('/tasks/export/excel', [TaskController::class, 'exportToExcel'])->name('tasks.export.excel');
    Route::get('/tasks/export/google-sheets', [TaskController::class, 'exportToGoogleSheets'])->name('tasks.export.google-sheets');

    // =======================
    // Notification Routes - FIXED
    // =======================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    
    // Hanya satu route untuk unread count
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    
    // Hanya satu route untuk mark as read
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');
    Route::delete('/notifications/deleteAll', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');

    // API untuk dropdown notification - gunakan prefix yang berbeda
    Route::get('/notifications/api/list', [NotificationController::class, 'api'])->name('notifications.api');
});