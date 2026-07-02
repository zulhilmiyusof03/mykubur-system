<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GraveRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('mykubur');
});

Route::prefix('grave-records')->group(function () {
    Route::get('/', [GraveRecordController::class, 'index']);
    Route::get('/search', [GraveRecordController::class, 'search']);
    Route::get('/capacities', [GraveRecordController::class, 'capacities']);
    Route::post('/rows', [GraveRecordController::class, 'addRows']);
    Route::delete('/rows', [GraveRecordController::class, 'deleteRow']);
    Route::post('/', [GraveRecordController::class, 'store']);
    Route::put('/{graveRecord}', [GraveRecordController::class, 'update']);
    Route::delete('/{graveRecord}', [GraveRecordController::class, 'destroy']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});
