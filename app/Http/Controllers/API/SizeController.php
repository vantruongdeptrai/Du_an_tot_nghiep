<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index()
    {
        return response()->json(Size::all(), 200);
    }

    public function show($id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['message' => 'Size này không tồn tại'], 404);
        }

        return response()->json($size, 200);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:sizes,name']);

        $size = Size::create($request->all());

        return response()->json($size, 201);
    }

    public function update(Request $request, $id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['message' => 'Size này không tồn tại'], 404);
        }

        $request->validate(['name' => 'unique:sizes,name,' . $size->id]);

        $size->update($request->all());

        return response()->json($size, 200);
    }

    public function destroy($id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['message' => 'Size này không tồn tại'], 404);
        }

        $size->delete(); 

        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $size = Size::withTrashed()->find($id);

        if (!$size) {
            return response()->json(['message' => 'Size này không tồn tại'], 404);
        }

        $size->restore();

        return response()->json(['message' => 'Size restored successfully'], 200);
    }
}