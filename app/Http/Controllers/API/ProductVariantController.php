<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class ProductVariantController extends Controller
{

    public function index()
    {
        return ProductVariant::all();
    }

    public function show($id)
    {
        $productVariant = ProductVariant::findOrFail($id);

        $now = now(); 
        if ($productVariant->sale_price && $productVariant->sale_start && $productVariant->sale_end) {
            if ($now->between($productVariant->sale_start, $productVariant->sale_end)) {
                $productVariant->display_price = $productVariant->sale_price;
            } else {
                $productVariant->display_price = $productVariant->price;
            }
        } else {
            $productVariant->display_price = $productVariant->price;
        }
    
        return response()->json($productVariant);
    }
    
    public function store(Request $request)
    {

        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'colors' => 'required|array|min:1',
            'colors.*' => 'required|integer|exists:colors,id',
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|integer|exists:sizes,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'images' => 'sometimes|array',
            'images.*' => 'nullable|string', 
        ];

        $validatedData = $request->validate($rules);

        if (count($validatedData['colors']) !== count($validatedData['sizes'])) {
            return response()->json([
                'message' => '.'
            ], 422);
        }

        $createdVariants = [];

        DB::beginTransaction();

        try {
            for ($i = 0; $i < count($validatedData['colors']); $i++) {
                $color_id = $validatedData['colors'][$i];
                $size_id = $validatedData['sizes'][$i];
                $quantityKey = "{$color_id}-{$size_id}";
                $priceKey = "{$color_id}-{$size_id}";

                $productVariant = new ProductVariant();
                $productVariant->product_id = $validatedData['product_id'];
                $productVariant->color_id = $color_id;
                $productVariant->size_id = $size_id;
                $productVariant->quantity = $validatedData['quantities'][$quantityKey] ?? 0;
                $productVariant->price = $validatedData['prices'][$priceKey] ?? 0;
                $productVariant->status = $validatedData['status'];

                $randomString = Str::upper(Str::random(5)); 
                $productVariant->sku = "SKU-{$validatedData['product_id']}-{$color_id}-{$size_id}-{$randomString}";

                if (isset($validatedData['images'][$quantityKey])) {
                    $imagePath = $validatedData['images'][$quantityKey];
                    $productVariant->image = $imagePath; // Gán đường dẫn hình ảnh
                } 
                $productVariant->save();
                $createdVariants[] = $productVariant;
            }

            DB::commit();

            return response()->json([
                'message' => 'tạo thành công.',
                'variants' => $createdVariants
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'khong thể thạo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'color_id' => 'required|integer|exists:colors,id',
            'size_id' => 'required|integer|exists:sizes,id',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after_or_equal:sale_start',
            'status' => 'required|boolean',
            'sku' => 'required|string|max:50|unique:product_variants,sku,' . $id,
            'image' => 'nullable|string', // Đường dẫn ảnh có thể là null
        ];
    
        $validatedData = $request->validate($rules);
    
        DB::beginTransaction();
    
        try {
            $productVariant = ProductVariant::findOrFail($id);
    
            $productVariant->product_id = $validatedData['product_id'];
            $productVariant->color_id = $validatedData['color_id'];
            $productVariant->size_id = $validatedData['size_id'];
            $productVariant->quantity = $validatedData['quantity'];
            $productVariant->price = $validatedData['price'];
            $productVariant->sale_price = $validatedData['sale_price'];
            $productVariant->sale_start = $validatedData['sale_start'];
            $productVariant->sale_end = $validatedData['sale_end'];
            $productVariant->status = $validatedData['status'];
            $productVariant->sku = $validatedData['sku'];
    
            // Cập nhật hình ảnh nếu có
            if (isset($validatedData['image'])) {
                $productVariant->image = $validatedData['image'];
            }
    
            $productVariant->save();
    
            DB::commit();
    
            return response()->json([
                'message' => 'ok.',
                'variant' => $productVariant
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'ok',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function destroy($id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->delete();
        return response()->json(null, 204);
    }

}