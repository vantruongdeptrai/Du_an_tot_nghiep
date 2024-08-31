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
        $categories = Category::all();
        // dd($categories);
        return response()->json($categories);
    }

   
   
    public function store(Request $request)
    {
        $data=[
            'name'=>$request->name,
            'image'=>$request->image,
            'slug'=>Str::slug($request->name)
        ];
        if (!empty($data['image'])) {
            $data['image'] = Storage::put('categories',$data['image']);
        }
      
        $category=Category::create($data);
        $imageUrl = isset($category->image) ? asset('storage/' . $category->image) : null;
        return response()->json([
            'message' => 'success',
            'category' => $category,
            'image_url' => $imageUrl
        ]);
    }

    
    public function update(Request $request, string $id)
    {
        $model=Category::query()->findOrFail($id);  
        $data=[
            'name'=>$request->name,
            'slug'=>Str::slug($request->name)
        ];
        // check có ảnh thì cho vào storage
        if ($request->hasFile('image')) {
            $data['image'] = Storage::put('categories', $request->file('image'));
        } 
        // lưu ảnh cũ trước khi update
        $imageCurrent=$model->image; 
        // update data mới
        $model->update($data);
      // ảnh cũ tồn tại trong storage thì xóa
        if ($request->hasFile('image') && $imageCurrent && Storage::exists($imageCurrent)) {
            Storage::delete($imageCurrent);
        } 
        return response()->json([
            'message' => 'success',
            'data' => array_merge($data, [
        'image_url' => isset($data['image']) ? asset('storage/' . $data['image']) : null
    ])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model=Category::query()->findOrFail($id);
        $model->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}