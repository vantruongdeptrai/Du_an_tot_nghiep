<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        return response()->json(Color::all(), 200);
    }

    public function show($id)
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['message' => 'Color not found'], 404);
        }

        return response()->json($color, 200);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:colors,name']);

        $color = Color::create($request->all());

        return response()->json($color, 201);
    }

    public function update(Request $request, $id)
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['message' => 'Color not found'], 404);
        }

        $request->validate(['name' => 'unique:colors,name,' . $color->id]);

        $color->update($request->all());

        return response()->json($color, 200);
    }

    public function destroy($id)
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['message' => 'Color not found'], 404);
        }

        $color->delete(); // Xóa mềm

        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $color = Color::withTrashed()->find($id);

        if (!$color) {
            return response()->json(['message' => 'Color not found'], 404);
        }

        $color->restore(); // Khôi phục lại

        return response()->json(['message' => 'Color restored successfully'], 200);
    }
}