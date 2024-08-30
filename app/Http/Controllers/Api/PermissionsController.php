<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permissions;


class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Permissions = Permissions::All();
        return response()->json($Permissions);
    }

    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $Permissions = Permissions::create([
            'name' => $request->input('name'),
        ]);

        return response()->json($Permissions, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $Permissions = Permissions::findOrFail($id);

        $Permissions->name = $request->input('name', $Permissions->name); 
        $Permissions->save();

        return response()->json($Permissions);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Permissions = Permissions::findOrFail($id);
        $Permissions->delete();

        return response()->json(null, 204);
    }
}
