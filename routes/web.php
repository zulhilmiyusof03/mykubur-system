<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GraveRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('mykubur');
});

Route::prefix('grave-records')->group(function () {
    Route::get('/', [GraveRecordController::class, 'index']);
    Route::post('/', [GraveRecordController::class, 'store']);
    Route::put('/{graveRecord}', [GraveRecordController::class, 'update']);
    Route::delete('/{graveRecord}', [GraveRecordController::class, 'destroy']);
    Route::post('/reset-demo', [GraveRecordController::class, 'resetDemo']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

use Illuminate\Support\Facades\DB;

Route::get('/debug-db', function () {
    return [
        'db' => DB::connection()->getDatabaseName(),
        'host' => DB::getConfig('host'),
    ];
});

Route::get('/db-check', function () {
    return DB::connection()->getDatabaseName();
});
