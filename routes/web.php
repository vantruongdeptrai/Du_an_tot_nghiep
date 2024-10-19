<?php

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Route;
use App\Models\DetailVariant;
use App\Models\Product;

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
<<<<<<< HEAD
    // $product_variants = ProductVariant::with('detailVariants')->get();
    // foreach($product_variants as $item){
    //     dd($item->detailVariants);
    // }
    // $attribute_values = AttributeValue::get()->all();
    // dd($attribute_values);
    // return view('welcome');
=======
    $detail_product = Product::findOrFail(7);
    
    $id = $detail_product->id;
    
    $detail_product_variants = ProductVariant::where('product_id',$id)->get();
    dd($detail_product_variants);
    return view('welcome');
>>>>>>> be55ab23e864ec18561ed95c80325adfa0e34254
});

