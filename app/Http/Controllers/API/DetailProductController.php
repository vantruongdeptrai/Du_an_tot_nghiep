<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Size;
use App\Models\Color;
class DetailProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $id)
    {
        //
        $detail_product = Product::findOrFail($id);
    
        $id = $detail_product->id;
    
        $detail_product_variants = ProductVariant::where('product_id',$id)->get();
        $sizes = Size::query()->get();
        $colors = Color::query()->get();
        return response()->json([$detail_product,$detail_product_variants,$sizes,$colors]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
