<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        return Gallery::all();
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'images' => 'required|array',
            'images.*' => 'required|string',
        ]);

        $galleries = [];

        foreach ($validated['images'] as $image) {
            $galleries[] = Gallery::create([
                'product_id' => $validated['product_id'],
                'image' => $image,
            ]);
        }

        return response()->json($galleries, 201);
    }
    public function update(Request $request, $id)
{
    // Tìm gallery theo ID
    $gallery = Gallery::find($id);

    // Kiểm tra nếu không tìm thấy gallery
    if (!$gallery) {
        return response()->json(['message' => 'Không tìm thấy gallery'], 404);
    }

    // Xác thực yêu cầu
    $validated = $request->validate([
        'image' => 'nullable|string', // Trường image là tùy chọn
    ]);

    // Nếu có ảnh mới, thay thế ảnh cũ
    if (isset($validated['image'])) {
        $gallery->update(['image' => $validated['image']]);
    }

    // Trả về dữ liệu đã cập nhật
    return response()->json($gallery);
}


    public function destroy($id)
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json(['message' => 'Không tìm thấy gallery'], 404);
        }

        $gallery->delete();

        return response()->json(['message' => 'Đã xóa gallery']);
    }
}
