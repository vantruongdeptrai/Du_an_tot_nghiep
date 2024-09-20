<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; 
use  Illuminate\Support\Facades\Storage;

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
        $products = Product::with([
            'productVariants' => function ($query) {
                $query->with('detailVariants.attributeValue');
            }
        ],'category')->get();
        $products->each(function ($product) {
            $product->category_name = $product->category->name; // Thêm trường category_name
        });
        return response()->json($products);

    }

    /**
     * Store a newly created resource in storage.
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
    
        $variants = $request->input('variants', default: []);
        foreach ($variants as $variant) {
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
        // Tìm sản phẩm cần cập nhật
    $product = Product::findOrFail($id);

    // Xử lý việc tải hình ảnh lên (nếu có hình ảnh mới)
    if ($request->hasFile('image')) {
        // Xóa hình ảnh cũ nếu có
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $image = $request->file('image');
        $imagePath = $image->store('images/products', 'public'); 
    } else {
        $imagePath = $product->image; // Nếu không có hình ảnh mới, giữ nguyên hình ảnh cũ
    }

    // Cập nhật thông tin sản phẩm
    $product->update([
        'name' => $request->input('name'),
        'description' => $request->input('description'),
        'price' => $request->input('price'),
        'sale_price' => $request->input('sale_price'),
        'category_id' => $request->input('category_id'),
        'sale_start' => $request->input('sale_start', $product->sale_start),
        'sale_end' => $request->input('sale_end', $product->sale_end),
        'new_product' => $request->input('new_product', $product->new_product),
        'best_seller_product' => $request->input('best_seller_product', $product->best_seller_product),
        'featured_product' => $request->input('featured_product', $product->featured_product),
        'image' => $imagePath, 
    ]);

    // Cập nhật hoặc thêm mới các biến thể của sản phẩm
    $variants = $request->input('variants', []);

    // Xóa các biến thể cũ không có trong yêu cầu cập nhật
    $existingVariantIds = $product->variants->pluck('id')->toArray();
    $newVariantIds = array_column($variants, 'id');
    $variantIdsToDelete = array_diff($existingVariantIds, $newVariantIds);

    ProductVariant::whereIn('id', $variantIdsToDelete)->delete();

    // Cập nhật hoặc thêm các biến thể mới
    foreach ($variants as $variant) {
        if (isset($variant['id'])) {
            // Nếu đã có variant, thì cập nhật
            $productVariant = ProductVariant::find($variant['id']);
            $productVariant->update([
                'quantity' => $variant['quantity'],
                'price' => $variant['price'],
                'status' => $variant['status'] ?? 0,
            ]);
        } else {
            // Nếu chưa có variant, thì thêm mới
            $sku = strtoupper(str_replace(' ', '-', $product->name) . '-' . Str::random(5));
            $productVariant = ProductVariant::create([
                'product_id' => $product->id,
                'quantity' => $variant['quantity'],
                'price' => $variant['price'],
                'sku' => $sku,
                'status' => $variant['status'] ?? 0,
            ]);
        }

        // Cập nhật chi tiết biến thể
        foreach ($variant['attributes'] as $attributeId => $attributeValueId) {
            DB::table('detail_variants')->updateOrInsert(
                [
                    'product_variant_id' => $productVariant->id,
                    'attribute_value_id' => $attributeValueId,
                ],
                [
                    'product_variant_id' => $productVariant->id,
                    'attribute_value_id' => $attributeValueId,
                ]
            );
        }
    }

    return response()->json($product, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }
    
        // xoa cac bien the lien quan
        ProductVariant::where('product_id', $id)->delete();
    
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
    
        $product->delete();
    
        return response()->json(['message' => 'xóa thành công'], 200);
    }
}
