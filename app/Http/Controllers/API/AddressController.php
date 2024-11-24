<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    public function getAllData()
    {
        $users = User::with('addresses')->get()->map(function ($user) {
            $user->addresses = $user->addresses->map(function ($address) {
                $address->full_address = $address->full_address;  
                return $address;
            });
            return $user;
        });

        return response()->json([
            'message' => 'Lấy dữ liệu thành công',
            'data' => $users,
        ], 200);
    }

    public function addAddresses(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'addresses' => 'required|array',
            'addresses.*.street' => 'required|string|max:255',
            'addresses.*.ward' => 'required|string|max:255',
            'addresses.*.district' => 'required|string|max:255',
            'addresses.*.city' => 'required|string|max:255',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.is_default' => 'nullable|boolean',
        ]);

        $addresses = [];
        foreach ($validatedData['addresses'] as $addressData) {
            if ($addressData['is_default']) {
                Address::where('user_id', $validatedData['user_id'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $address = Address::create([
                'user_id' => $validatedData['user_id'],
                'street' => $addressData['street'],
                'ward' => $addressData['ward'],
                'district' => $addressData['district'],
                'city' => $addressData['city'],
                'zip_code' => $addressData['zip_code'] ?? null,
                'country' => $addressData['country'] ?? 'Vietnam',
                'is_default' => $addressData['is_default'] ?? false,
            ]);

            $address->full_address = $address->full_address;  
            $addresses[] = $address;
        }

        return response()->json([
            'message' => 'Thêm địa chỉ thành công',
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
            'street' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if (isset($validatedData['is_default']) && $validatedData['is_default']) {
            Address::where('user_id', $address->user_id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $address->update($validatedData);
        $address->full_address = $address->full_address;  

        return response()->json([
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $address,
        ], 200);
    }

    public function deleteAddress($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Địa chỉ không tồn tại'], 404);
        }

        // Soft delete address
        $address->delete();

        return response()->json(['message' => 'Xóa địa chỉ thành công'], 200);
    }
}


