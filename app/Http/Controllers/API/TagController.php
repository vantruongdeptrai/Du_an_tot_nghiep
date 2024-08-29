<?php

namespace App\Http\Controllers\API;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagController extends Controller
{
    function index(){
        $tags = Tag::all();
        return response()->json($tags);
    }

    function store(Request $req){
        $data = $req->all();
        $tag = new Tag();
        $tag -> name = $data['name'];
        $tag->save();
        return response()->json([
            'message' => 'success',
            'tag' => $tag
        ]);
    }

    function update(Request $req, $id){
        $tag = Tag::find($id);
        if(!$tag){
            return response()->json([
                'message' => 'unsuccess',
            ]);
        }
        $data = $req->all();
        $tag -> name = $data['name'];
        $tag->save();
        return response()->json([
            'message' => 'success',
            'tag' => $tag
        ]);
    }

    function destroy($id){
        $destroy = Tag::find($id);
        $destroy->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}
