<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
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
            'product_id' => 'required|integer',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'sku' => 'required|string|unique:product_variants',
            'status' => 'required|boolean',
            'image' => 'nullable|string', // Trường ảnh dưới dạng chuỗi
        ]);

        $productVariant = new ProductVariant();
        $productVariant->product_id = $request->product_id;
        $productVariant->color_id = $request->color_id;
        $productVariant->size_id = $request->size_id;
        $productVariant->quantity = $request->quantity;
        $productVariant->price = $request->price;
        $productVariant->sku = $request->sku;
        $productVariant->status = $request->status;

        // Lưu đường dẫn ảnh
        if ($request->has('image')) {
            $productVariant->image = $request->image;
        }

        $productVariant->save();
        return response()->json($productVariant, 201);
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