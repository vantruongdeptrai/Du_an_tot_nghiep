<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listColor=Color::all();
        return response()->json($listColor,200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $data=$request->validate([
            'name' => 'required|string|max:255',
        ]);
        $colors=Color::query()->create($data);
        return response()->json($colors,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $colors=Color::query()->findOrFail($id);
        return response()->json($colors,200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $color=Color::query()->findOrFail($id);
        $data=$request->validate([
            'name' => 'required|string|max:255',
        ]);
        $color->update($data);
        return response()->json($color,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $color=Color::query()->findOrFail($id);
        $color->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}

