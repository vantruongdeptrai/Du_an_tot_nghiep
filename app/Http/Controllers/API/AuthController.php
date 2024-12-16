<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Mail\WelcomeMail;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use function Laravel\Prompts\table;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // Kiểm tra xem có tìm thấy người dùng và mật khẩu nhập vào có đúng không.
        if ($user && Hash::check($request->password, $user->password)) {

            // Xóa tất cả các token cũ của người dùng trước khi tạo token mới
            $user->tokens()->delete();
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
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@(fpt\.edu\.vn|gmail\.com)$/'
            ],
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::query()->create($data);
        $token = $user->createToken('API Token')->plainTextToken;
        Mail::to($user->email)->queue(new \App\Mail\WelcomeMail($user));
        return response()->json([
            'token' => $token,
            'message' => 'đăng ký thành công',
        ], 201);
    }
    public function forgotPassword(Request $request)
    {
        // Validate email
        $validator =$request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@(fpt\.edu\.vn|gmail\.com)$/',
                'exists:users,email'
            ],
        ]);

        // Generate a new password
        $newPassword = Str::random(10);

        // Update the user's password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($newPassword);
        $user->save();

        // Send email using queue
        Mail::to($user->email)->queue(new ResetPasswordMail($newPassword));

        return response()->json([
            'success' => true,
            'message' => 'A new password has been sent to your email.'
        ], 200);
    }
    public function updatePassword(Request $request, $id_user)
    {
        // Tìm người dùng theo ID
        $user = User::find($id_user);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        // Kiểm tra dữ liệu yêu cầu (mật khẩu cũ, mật khẩu mới và xác nhận mật khẩu)
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed', // mật khẩu mới và xác nhận phải giống nhau
        ]);

        // Nếu có lỗi xác thực, trả về lỗi
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Kiểm tra mật khẩu cũ của người dùng
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu cũ không đúng'], 400);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Mật khẩu đã được cập nhật thành công'], 200);
    }

}
