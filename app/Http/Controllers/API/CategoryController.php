<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function show(string $id)
    {
        $category = Category::query()->findOrFail($id);
        return response()->json($category);
    }
   
    public function store(Request $request)
    {
        $data=[
            'name'=>$request->name,
            'image'=>$request->image,
            'slug'=>Str::slug($request->name)
        ];
        $category=Category::create($data);
    
        return response()->json([
            'message' => 'success',
            'category' => $category,
           
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
        if ($request->has('image') && $request->image !== null) {
            $data['image'] = $request->image;
        } else {
            $data['image'] =$model->image; 
        }
        // update data mới
        $model->update($data);
        return response()->json([
            'message' => 'success',
            'data' =>$data
    
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
