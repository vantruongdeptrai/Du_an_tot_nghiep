<?php

use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\CouponController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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