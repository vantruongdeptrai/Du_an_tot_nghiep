<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các category
        $categories = Category::all();

        // Thêm đường dẫn đầy đủ cho ảnh
        $categories = $categories->map(function ($category) {
            // Tạo image_url dựa trên đường dẫn ảnh
            $category->image_url = $category->image ? Storage::url($category->image) : null;
            return $category;
        });

        return response()->json([
            'message' => 'success',
            'categories' => $categories
        ]);
    }

    public function show(string $id)
    {
        $category = Category::query()->findOrFail($id);
        $category->image_url = $category->image ? asset('storage/' . $category->image) : null;
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:2048', // Chấp nhận file ảnh với các định dạng jpg, jpeg, png
        ]);

        // Lưu file ảnh vào storage
        $path = $request->file('image')->store('categories', 'public');

        $data = [
            'name' => $validatedData['name'],
            'image' => $path, // Lưu đường dẫn của ảnh vào cơ sở dữ liệu
            'slug' => Str::slug($validatedData['name']),
        ];

        // Tạo mới category với dữ liệu đã có
        $category = Category::create($data);

        return response()->json([
            'message' => 'success',
            'category' => $category,
        ]);
    }


    public function update(Request $request, string $id)
    {
        $model = Category::query()->findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Chấp nhận file ảnh với các định dạng jpg, jpeg, png
        ]);

        $data = [
            'name' => $validatedData['name'],
            'slug' => Str::slug($validatedData['name']),
        ];

        // Kiểm tra xem có file ảnh trong request
        if ($request->hasFile('image')) {
            // Nếu category có ảnh cũ, xóa ảnh cũ trước khi upload ảnh mới
            if ($model->image && Storage::exists($model->image)) {
                Storage::delete($model->image); // Xóa ảnh cũ khỏi storage
            }
            // Lưu file ảnh vào thư mục public/categories
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path; // Lưu đường dẫn của ảnh vào cơ sở dữ liệu
        } else {
            $data['image'] = $model->image; // Giữ nguyên ảnh cũ nếu không có ảnh mới
        }

        // Cập nhật dữ liệu mới vào model
        $model->update($data);

        return response()->json([
            'message' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Category::query()->findOrFail($id);
        $model->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}
