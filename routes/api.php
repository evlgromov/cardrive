<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\CarController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\CarRentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('users', [UserController::class, 'getAllUsersList']);

Route::get('cars', [CarController::class, 'getAllCarsList']);

Route::prefix('rents')->group(function () {
    Route::get('/', [CarRentController::class, 'getAllActiveRents']);
    Route::post('/new', [CarRentController::class, 'startRent']);
    Route::get('/{id}', [CarRentController::class, 'getRentById'])->where('id', '[0-9]+');
    Route::patch('/{id}', [CarRentController::class, 'editRent']);
    Route::delete('/{id}', [CarRentController::class, 'stopRent']);
    Route::get('/history', [CarRentController::class, 'rentsHistoryList']);
    Route::get('/history/{id}', [CarRentController::class, 'getHistoryById']);
});
