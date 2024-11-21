<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\User;


class AddressController extends Controller
{


    public function getAllData()
    {
        $users = User::with('addresses')->get();  

        return response()->json([
            'message' => 'Lấy dữ liệu thành công',
            'data' => $users,
        ], 200);
    }
    /**
     * Thêm nhiều địa chỉ cho một người dùng.
     */
    public function addAddresses(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'addresses' => 'required|array',
            'addresses.*' => 'required|string|max:255',
        ]);

        $addresses = [];
        foreach ($validatedData['addresses'] as $address) {
            $addresses[] = Address::create([
                'user_id' => $validatedData['user_id'],
                'address' => $address,
            ]);
        }

        return response()->json([
            'message' => 'thêm thành công',
            'data' => $addresses,
        ], 201);
    }

    public function updateAddress(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Địa chỉ không tồn tại'], 404);
        }

        $validatedData = $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $address->update($validatedData);

        return response()->json([
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $address,
        ], 200);
    }

    /**
     * Xóa một địa chỉ của người dùng.
     */
    public function deleteAddress($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'không thành công'], 404);
        }

        $address->delete();

        return response()->json(['message' => 'xóa thánhf công']);
    }


}
