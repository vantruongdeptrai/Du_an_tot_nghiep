<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionsController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::controller(RoleController::class)
    ->group(function () {
        Route::get('/roles', 'index')->name('roles.index');
        Route::post('/roles', 'store')->name('roles.store');
        Route::put('/roles/{id}', 'update')->name('roles.update');
        Route::delete('/roles/{id}', 'destroy')->name('roles.destroy');
    });

Route::controller(PermissionsController::class)
    ->group(function () {
        Route::get('/permissions', 'index')->name('permissions.index');
        Route::post('/permissions', 'store')->name('permissions.store');
        Route::put('/permissions/{id}', 'update')->name('permissions.update');
        Route::delete('/permissions/{id}', 'destroy')->name('permissions.destroy');
    });









