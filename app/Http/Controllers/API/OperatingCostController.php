<?php

namespace App\Http\Controllers\API;
use App\Models\OperatingCost;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OperatingCostController extends Controller
{
    function index(){
        $operating_costs = OperatingCost::all();
        return response()->json($operating_costs);
    }
    function store(Request $req){
        $data = $req->all();
        $operating_cost = new OperatingCost();
        $operating_cost -> cost_type = $data['cost_type'];
        $operating_cost -> amount = $data['amount'];
        $operating_cost -> description = $data['description'];
        $operating_cost->save();
        return response()->json([
            'message' => 'success',
            'operating_cost' => $operating_cost
        ]);
    }
    function update(Request $req, $id){
        $operating_cost = OperatingCost::find($id);
        if(!$operating_cost){
            return response()->json([
                'message' => 'unsuccess',
            ]);
        }
        $data = $req->all();
        $operating_cost -> cost_type = $data['cost_type'];
        $operating_cost -> amount = $data['amount'];
        $operating_cost -> description = $data['description'];
        $operating_cost->save();
        return response()->json([
            'message' => 'success',
            'operating_cost' => $operating_cost
        ]);
    }
    function destroy($id){
        $destroy = OperatingCost::find($id);
        $destroy->delete();
        return response()->json([
            'message' => 'success'
        ]);
    }
}
