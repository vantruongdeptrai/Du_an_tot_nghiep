<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lisSize=Size::all();
        return response()->json($lisSize,200);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create(Request $request)
    // {
      
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $data=$request->validate([
            'name' => 'required|string|max:255',
        ]);
        $size=Size::query()->create($data);
        return response()->json($size,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $size=Size::query()->findOrFail($id);
        return response()->json($size,200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $size=Size::query()->findOrFail($id);
        $data=$request->validate([
            'name' => 'required|string|max:255',
        ]);
        $size->update($data);
        return response()->json($size,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $size=Size::query()->findOrFail($id);
        $size->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}
