<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\PermissionsController;
use App\Http\Controllers\API\OperatingCostController;
use App\Http\Controllers\API\AttributeValueController;



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

//http://127.0.0.1:8000/api/coupons
Route::get('/coupons', [CouponController::class, 'index']); 
//http://127.0.0.1:8000/api/coupons          
Route::post('/coupons', [CouponController::class, 'store']); 
//http://127.0.0.1:8000/api/coupons/{id}          
Route::get('/coupons/{id}', [CouponController::class, 'show']); 
//http://127.0.0.1:8000/api/coupons/{id}      
Route::put('/coupons/{id}', [CouponController::class, 'update']);     
//http://127.0.0.1:8000/api/coupons/{id}
Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);  

//http://127.0.0.1:8000/api/attributes
Route::get('/attributes', [AttributeController::class, 'index']);     
//http://127.0.0.1:8000/api/attributes  
Route::post('/attributes', [AttributeController::class, 'store']);
//http://127.0.0.1:8000/api/attributes/{id}      
Route::get('/attributes/{id}', [AttributeController::class, 'show']);
//http://127.0.0.1:8000/api/attributes/{id}   
Route::put('/attributes/{id}', [AttributeController::class, 'update']);
//http://127.0.0.1:8000/api/attributes/{id} 
Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);

Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);
Route::put('/tags/{id}', [TagController::class, 'update']);
Route::delete('/tags/{id}', [TagController::class, 'destroy']);


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

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

Route::get('attribute-values', [AttributeValueController::class, 'index']);

Route::post('attribute-values', [AttributeValueController::class, 'store']);

Route::get('attribute-values/{id}', [AttributeValueController::class, 'show']); //lấy theo id của bảng AttributeValues

Route::get('attribute-values/attribute/{attributeId}', [AttributeValueController::class, 'showByAttributeId']);// lấy theo attribute_id

Route::put('attribute-values/{id}', [AttributeValueController::class, 'update']);

Route::delete('attribute-values/{id}', [AttributeValueController::class, 'destroy']);