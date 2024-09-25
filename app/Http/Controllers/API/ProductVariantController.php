<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index()
    {
        return response()->json(ProductVariant::all(), 200);
    }

    public function show($id)
    {
        $productVariant = ProductVariant::find($id);

        if (!$productVariant) {
            return response()->json(['message' => 'Product variant not found'], 404);
        }

        return response()->json($productVariant, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'sku' => 'required|unique:product_variants,sku',
        ]);

        $productVariant = ProductVariant::create($request->all());

        return response()->json($productVariant, 201);
    }

    public function update(Request $request, $id)
    {
        $productVariant = ProductVariant::find($id);

        if (!$productVariant) {
            return response()->json(['message' => 'Product variant not found'], 404);
        }

        $request->validate([
            'sku' => 'unique:product_variants,sku,' . $productVariant->id,
        ]);

        $productVariant->update($request->all());

        return response()->json($productVariant, 200);
    }

    public function destroy($id)
    {
        $productVariant = ProductVariant::find($id);

        if (!$productVariant) {
            return response()->json(['message' => 'Product variant not found'], 404);
        }

        $productVariant->delete(); // Xóa mềm

        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $productVariant = ProductVariant::withTrashed()->find($id);

        if (!$productVariant) {
            return response()->json(['message' => 'Product variant not found'], 404);
        }

        $productVariant->restore(); // Khôi phục lại

        return response()->json(['message' => 'Product variant restored successfully'], 200);
    }
}