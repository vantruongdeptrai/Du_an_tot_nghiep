<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; 


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

    


    /**
     * Display the specified resource.
     */
    public function store(Request $request)
    {
        // Xử lý việc tải hình ảnh lên
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images/products', 'public'); 
        }
    
        // Them san pham
        $product = Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'sale_price' => $request->input('sale_price'),
            'category_id' => $request->input('category_id'),
            'sale_start' => now(), 
            'sale_end' => now()->addDays(7),  
            'new_product' => 0,
            'best_seller_product' => 0,
            'featured_product' => 0,
            'image' => $imagePath, 
        ]);
    
        $variants = $request->input('variants', []);
        foreach ($variants as $variant) {
            // Generate SKU based on product name
            $sku = strtoupper(str_replace(' ', '-', $product->name) . '-' . Str::random(5));
    
            $productVariant = ProductVariant::create([
                'product_id' => $product->id,
                'quantity' => $variant['quantity'],
                'price' => $variant['price'],
                'sku' => $sku, // Use the generated SKU
                'status' => $variant['status'] ?? 0,
            ]);
    
            foreach ($variant['attributes'] as $attributeId => $attributeValueId) {
                DB::table('detail_variants')->insert([
                    'product_variant_id' => $productVariant->id,
                    'attribute_value_id' => $attributeValueId,
                ]);
            }
        }
    
        return response()->json($product, 201);
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
    public function destroy($id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        // xoa cac bien the lien quan
        ProductVariant::where('product_id', $id)->delete();
    
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
    
        $product->delete();
    
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
    
}
