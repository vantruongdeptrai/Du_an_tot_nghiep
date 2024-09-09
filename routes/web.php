<?php

use Illuminate\Support\Facades\Route;
use App\Models\Attribute;
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
    $attribute_id = Attribute::query()->get()->pluck('id');//array attribute_id
    dd($attribute_id);
    return view('welcome');
});

