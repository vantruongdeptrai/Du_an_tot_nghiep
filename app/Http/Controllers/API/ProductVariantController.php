<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ProductVariant;


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
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'colors' => 'required|array',
            'sizes' => 'required|array',
            'quantities' => 'required|array',
            'prices' => 'required|array',
            'images' => 'required|array',
            'status' => 'nullable|string', // Nếu status là một chuỗi hoặc có thể để null
        ]);

        // Lấy product_id
        $productId = $request->product_id;

        // Lấy colors, sizes, quantities, images, và prices từ request
        $colors = $request->colors;
        $sizes = $request->sizes;
        $quantities = $request->quantities;
        $prices = $request->prices;
        $images = $request->images;
        $status = $request->status;

        // Giả sử colors và sizes là các mảng có cùng độ dài
        foreach ($colors as $index => $colorId) {
            $sizeId = $sizes[$index];

            // Tạo khóa để truy cập vào quantities, images, và prices
            $key = "$colorId-$sizeId";

            // Lấy giá trị từ quantities, images, và prices tương ứng với khóa
            $quantity = isset($quantities[$key]) ? $quantities[$key] : null;
            $price = isset($prices[$key]) ? $prices[$key] : null;
            $image = isset($images[$key]) ? $images[$key] : null;

            // Kiểm tra nếu tất cả dữ liệu cần thiết tồn tại
            if ($quantity !== null && $price !== null && $image !== null) {
                // Lưu biến thể sản phẩm
                ProductVariant::create([
                    'product_id' => $productId,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'image' => $image,
                    'status' => $status ?? 1, // Nếu status null, gán mặc định là active (1)
                ]);
            }
        }

        return response()->json(['message' => 'Product variants added successfully']);
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