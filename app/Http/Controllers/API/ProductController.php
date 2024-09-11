<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::get()->all();//admin

        $product_variants = ProductVariant::with('detailVariants')->get();//client

        return response()->json([$products,$product_variants]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Create the product
        $product = Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'sale_price' => $request->input('sale_price'),
            'category_id' => $request->input('category_id'),
            'sale_start' => now(),  // Assuming current time for sale start
            'sale_end' => now()->addDays(7),  // Sale ends after 7 days
            'new_product' => 0,
            'best_seller_product' => 0,
            'featured_product' => 0
        ]);

        // Create product variants
        $variants = $request->input('variants');
        foreach ($variants as $variant) {
            ProductVariant::create([
                'product_id' => $product->id,
                'quantity' => $variant['quantity'],
                'price' => $variant['price'],
                'sku' => $variant['sku'],
                'status' => $variant['status']
            ]);
        }

        return response()->json($product->load('variants'), 201);
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
