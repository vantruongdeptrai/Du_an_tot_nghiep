<?php

namespace App\Http\Controllers\API;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(){
        $tags = Tag::all();
        return response()->json($tags);
    }
    public function show($id){
        $tags = Tag::find($id);
        return response()->json($tags);
    }

    public function store(Request $req){
        $data = $req->all();
        $tag = new Tag();
        $tag -> name = $data['name'];
        $tag->save();
        return response()->json([
            'message' => 'success',
            'tag' => $tag
        ]);
    }

    public function update(Request $req, $id){
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

    public function destroy($id){
        $destroy = Tag::find($id);
        $destroy->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}
