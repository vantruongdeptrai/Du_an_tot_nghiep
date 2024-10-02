<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        
        // Kiểm tra xem có tìm thấy người dùng và mật khẩu nhập vào có đúng không.
        if ($user && Hash::check($request->password, $user->password)) {
            // Tạo token cho người dùng
            $token = $user->createToken('API Token')->plainTextToken;
    
            // Xác định vai trò của người dùng
            $role = $user->role->name;  // Giả sử bảng role có cột 'name' để lưu tên vai trò
    
            return response()->json([
                'user' => $user,
                'role' => $role,
                'token' => $token,
            ]);
        }
    
        return response()->json(['message' => 'unsuccessful'], 401);
    }
    public function logout(Request $request)
    {
        // Xóa token hiện tại của người dùng đã đăng nhập
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công!'
        ]);
    }
}
