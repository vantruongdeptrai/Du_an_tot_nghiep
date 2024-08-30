<?php
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\OperatingCostController;
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

Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);
Route::put('/tags/{id}', [TagController::class, 'update']);
Route::delete('/tags/{id}', [TagController::class, 'destroy']);

Route::get('/operating-costs', [OperatingCostController::class, 'index']);
Route::post('/operating-costs', [OperatingCostController::class, 'store']);
Route::put('/operating-costs/{id}', [OperatingCostController::class, 'update']);
Route::delete('/operating-costs/{id}', [OperatingCostController::class, 'destroy']);

Route::get('/roles', [RoleController::class, 'index']);
//http://127.0.0.1:8000/api/roles
Route::post('/roles', [RoleController::class, 'store']);
//http://127.0.0.1:8000/api/roles
Route::put('/roles/{id}', [RoleController::class, 'update']);
//http://127.0.0.1:8000/api/roles/{id}
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
//http://127.0.0.1:8000/api/roles/{id}


Route::get('/permissions', [PermissionsController::class, 'index']);
//http://127.0.0.1:8000/api/permissions
Route::post('/permissions', [PermissionsController::class, 'store']);
//http://127.0.0.1:8000/api/permissions
Route::put('/permissions/{id}', [PermissionsController::class, 'update']);
//http://127.0.0.1:8000/api/permissions/{id}
Route::delete('/permissions/{id}', [PermissionsController::class, 'destroy']);
//http://127.0.0.1:8000/api/permissions/{id}

//


