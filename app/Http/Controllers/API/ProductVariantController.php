<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;


class ProductVariantController extends Controller
{
    public function index()
    {
        return ProductVariant::all();
    }

    public function show($id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        return response()->json($productVariant);
    }
    public function store(Request $request)
    {

        $createdVariants = [];
    
        foreach ($request->colors as $color_id) {
            foreach ($request->sizes as $size_id) {
                $productVariant = new ProductVariant();
                $productVariant->product_id = $request->product_id;
                $productVariant->color_id = $color_id;
                $productVariant->size_id = $size_id;
    
                $quantityKey = $color_id . '-' . $size_id; 
                $productVariant->quantity = $request->quantities[$quantityKey] ?? 0; 
    
                $priceKey = $color_id . '-' . $size_id; 
                $productVariant->price = $request->prices[$priceKey] ?? 0; 
    
                $productVariant->status = $request->status;
    
                $randomString = Str::random(5); // Generate a 5-character random string
                $productVariant->sku = 'SKU-' . $request->product_id . '-' . $color_id . '-' . $size_id . '-' . $randomString;
    
                if (isset($request->images)) {
                    $imageKey = $color_id . '-' . $size_id;
                    $productVariant->image = $request->images[$imageKey] ?? null;
                }
    
                $productVariant->save();
    
                $createdVariants[] = $productVariant;
            }

        $request->validate([
            'product_id' => 'required|integer',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required|boolean',
            'image' => 'nullable|string', // Trường ảnh dưới dạng chuỗi
        ]);

        $productVariant = new ProductVariant();
        $productVariant->product_id = $request->product_id;
        $productVariant->color_id = $request->color_id;
        $productVariant->size_id = $request->size_id;
        $productVariant->quantity = $request->quantity;
        $productVariant->price = $request->price;
        $productVariant->sku = Str::upper(Str::random(8));
        $productVariant->status = $request->status;

        // Lưu đường dẫn ảnh
        if ($request->has('image')) {
            $productVariant->image = $request->image;

        }
    
        return response()->json([
            'message' => 'Product variants created successfully',
            'variants' => $createdVariants
        ], 201);
    }
    

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'sku' => 'required|string|unique:product_variants,sku,' . $id,
            'status' => 'required|boolean',
            'image' => 'nullable|string',
        ]);

        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->product_id = $request->product_id;
        $productVariant->color_id = $request->color_id;
        $productVariant->size_id = $request->size_id;
        $productVariant->quantity = $request->quantity;
        $productVariant->price = $request->price;
        $productVariant->sku = $request->sku;
        $productVariant->status = $request->status;

        // Cập nhật trường ảnh
        if ($request->has('image')) {
            $productVariant->image = $request->image;
        }

        $productVariant->save();
        return response()->json($productVariant);
    }

    public function destroy($id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->delete();
        return response()->json(null, 204);
    }

   
}