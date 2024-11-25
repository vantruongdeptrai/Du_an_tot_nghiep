<?php
namespace App\Http\Controllers\API;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
    $productVariant = ProductVariant::with(['product', 'size', 'color'])->findOrFail($id);

    $now = now(); 
    $displayPrice = $productVariant->price;
    if ($productVariant->sale_price && $productVariant->sale_start && $productVariant->sale_end) {
        if ($now->between($productVariant->sale_start, $productVariant->sale_end)) {
            $displayPrice = $productVariant->sale_price;
        }
    }
    return response()->json([
        "id" => $productVariant->id,
        "product_name" => $productVariant->product->name ?? null,
        "color_name" => $productVariant->color->name ?? null,
        "size_name" => $productVariant->size->name ?? null,
        "quantity" => $productVariant->quantity,
        "image" => $productVariant->image,
        "price" => $productVariant->price,
        "sale_price" => $productVariant->sale_price,
        "sale_start" => $productVariant->sale_start,
        "sale_end" => $productVariant->sale_end,
        "sku" => $productVariant->sku,
        "status" => $productVariant->status,
        "deleted_at" => $productVariant->deleted_at,
        "created_at" => $productVariant->created_at,
        "updated_at" => $productVariant->updated_at,
        "display_price" => $displayPrice,
        "image_url" => url('storage/' . $productVariant->image),
        "final_price" => $displayPrice,
    ]);
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
        'images.*' => 'nullable|file', 
    ];

    $validatedData = $request->validate($rules);

    if (count($validatedData['colors']) !== count($validatedData['sizes'])) {
        return response()->json([
            'message' => 'số lượng màu săc phải phù hợp'
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

                    // Lưu ảnh vào storage nếu tồn tại
                    if (isset($validatedData['images'][$quantityKey]) && $validatedData['images'][$quantityKey]) {
                        $path = $validatedData['images'][$quantityKey]->store('product_variants', 'public');
                        $productVariant->image = $path;
                    }


                    $productVariant->save();
                    $createdVariants[] = $productVariant;
        }

        DB::commit();

        return response()->json([
            'message' => 'thêm sản phẩm biến thể thành công',
            'variants' => $createdVariants
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'thêm biến thể thất bại',
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
        'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', 
    ];

    $validatedData = $request->validate($rules);

    DB::beginTransaction(); 

    try {
        $productVariant = ProductVariant::findOrFail($id);

        // Cập nhật dữ liệu sản phẩm biến thể
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

        // Xử lý cập nhật ảnh mới nếu có
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu tồn tại
            if ($productVariant->image) {
                Storage::disk('public')->delete($productVariant->image);
            }

            // Lưu ảnh mới vào storage
            $path = $request->file('image')->store('product_variants', 'public');
            $productVariant->image = $path;
        }

        $productVariant->save();

        DB::commit(); // Xác nhận giao dịch

        return response()->json([
            'message' => 'sửa biến thể thành công',
            'variant' => $productVariant,
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack(); 

        return response()->json([
            'message' => 'sửa biến thể thất bại',
            'error' => $e->getMessage(),
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