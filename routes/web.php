<?php

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Route;
use App\Models\DetailVariant;
use App\Models\Product;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    dd(Hash::make('123456789'));
    return view('welcome');
});