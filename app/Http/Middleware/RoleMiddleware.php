<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
          // Kiểm tra người dùng đã đăng nhập
          if (Auth::check()) {
            // Lấy vai trò của người dùng
            $userRole = Auth::user()->role->name;

            // Kiểm tra nếu vai trò người dùng nằm trong danh sách roles
            if (in_array($userRole, $roles)) {
                return $next($request);
            }
        }

        // Trả về lỗi nếu người dùng không có quyền truy cập
        return response()->json(['message' => 'unsuccessful'], 403);
    
    }
}
