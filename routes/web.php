<?php

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Route;
use App\Models\DetailVariant;
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
    // $product_variants = ProductVariant::with('detailVariants')->get();
    // foreach($product_variants as $item){
    //     dd($item->detailVariants);
    // }
    // $attribute_values = AttributeValue::get()->all();
    // dd($attribute_values);
    // return view('welcome');
});

