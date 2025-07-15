<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SoapController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth', [AuthController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::get('/tasks/export-xml', [TaskController::class, 'exportXml']);
    Route::post('/tasks/restore-xml', [TaskController::class, 'restoreFromXml']);

    Route::post('/tasks/send-soap', [SoapController::class, 'sendTasksToExternalSoap']);
});