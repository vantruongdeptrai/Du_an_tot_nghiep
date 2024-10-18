<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    //
    public function index()
    {
        $blogs = Blog::query()->get();
        return response()->json($blogs);
    }

   
   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'image' => 'required|string',
            'content_blog' => 'required',
        ]);

        $blog = Blog::create($validatedData);

        return response()->json($blog, 201);
    }

    
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|required|string',
            'content_blog' => 'sometimes|required',
        ]);

        $blog = Blog::findOrFail($id);
        $blog->update($validatedData);

        return response()->json($blog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully']);
    }
}
