<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    // Lấy danh sách tất cả attributes
    public function index()
    {
        return response()->json(Attribute::all());
    }

    // Tạo một attribute mới
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $attribute = Attribute::create($request->all());
        return response()->json($attribute, 201);
    }

    // Lấy một attribute theo ID
    public function show($id)
    {
        $attribute = Attribute::find($id);
        if ($attribute) {
            return response()->json($attribute);
        }
        return response()->json(['message' => 'Attribute not found'], 404);
    }

    // Cập nhật một attribute
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $attribute = Attribute::find($id);
        if ($attribute) {
            $attribute->update($request->all());
            return response()->json($attribute);
        }
        return response()->json(['message' => 'Attribute not found'], 404);
    }

    // Xóa một attribute
    public function destroy($id)
    {
        $attribute = Attribute::find($id);
        if ($attribute) {
            $attribute->delete();
            return response()->json(['message' => 'Attribute deleted']);
        }
        return response()->json(['message' => 'Attribute not found'], 404);
    }
}