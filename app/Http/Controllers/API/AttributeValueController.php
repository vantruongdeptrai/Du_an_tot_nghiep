<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\AttributeValue;
use App\Http\Controllers\Controller;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributeValues = AttributeValue::with('attribute')->get();
        return response()->json($attributeValues);
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
        $attributeValue = AttributeValue::create($request->all());
        return response()->json($attributeValue, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attributeValue = AttributeValue::with('attribute')->findOrFail($id);
        return response()->json($attributeValue);
    }
    public function showByAttributeId($attributeId)
    {
        $attributeValues = AttributeValue::where('attribute_id', $attributeId)->get();

        if ($attributeValues->isEmpty()) {
            return response()->json(['message' => 'No values found for this attribute'], 404);
        }

        return response()->json($attributeValues);
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
        $attributeValue = AttributeValue::query()->findOrFail($id);
        $attributeValue->update($request->all());
        return response()->json([
            'message' => 'success',
            'attributeValue' =>$attributeValue
    
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attributeValue = AttributeValue::findOrFail($id);
        $attributeValue->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}
