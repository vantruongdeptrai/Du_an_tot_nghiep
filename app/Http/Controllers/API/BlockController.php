<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Block;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index()
    {
        $blocks = Block::withTrashed()->with('user')->get();
        return response()->json($blocks);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', 
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'content_blog' => 'required|string',
        ]);

        $block = Block::create($validated);
        return response()->json($block, 201);
    }
    public function update(Request $request, $id)
    {
        $block = Block::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id', // Kiểm tra user_id
            'title' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|required|string',
            'content_blog' => 'sometimes|required|string',
        ]);

        $block->update($validated);
        return response()->json($block);
    }
    public function destroy($id)
    {
        $block = Block::findOrFail($id);
        $block->delete();
        return response()->json(['message' => 'Block soft deleted']);
    }
}
