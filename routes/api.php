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


Route::get('/coupons', [CouponController::class, 'index']);       
Route::post('/coupons', [CouponController::class, 'store']);     
Route::get('/coupons/{id}', [CouponController::class, 'show']);   
Route::put('/coupons/{id}', [CouponController::class, 'update']); 
Route::delete('/coupons/{id}', [CouponController::class, 'destroy']); 

Route::get('/attributes', [AttributeController::class, 'index']);     
Route::post('/attributes', [AttributeController::class, 'store']);    
Route::get('/attributes/{id}', [AttributeController::class, 'show']);  
Route::put('/attributes/{id}', [AttributeController::class, 'update']);
Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);