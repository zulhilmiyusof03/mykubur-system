<?php

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
