<?php

namespace App\Http\Controllers\API;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CommentController extends Controller
{
    public function index($id)
{
    $product = Product::findOrFail($id);

    // Lấy tất cả bình luận của sản phẩm, bao gồm cả reply, và thông tin người dùng (ảnh, tên)
    $comments = $product->comments()->with(['user' => function($query) {
        $query->select('id', 'name', 'image'); // Chọn trường 'id', 'name', 'image'
    }, 'replyComments.user' => function($query) {
        $query->select('id', 'name', 'image'); // Chọn trường 'id', 'name', 'image' cho reply
    }])->get();

    // Thêm đường dẫn ảnh vào các bình luận
    $comments->each(function ($comment) {
        // Kiểm tra nếu có ảnh người dùng, thêm URL ảnh
        if ($comment->user->image) {
            $comment->user->image_url = Storage::url($comment->user->image);
        }

        // Thêm ảnh vào reply comments nếu có
        $comment->replyComments->each(function ($reply) {
            if ($reply->user->image) {
                $reply->user->image_url = Storage::url($reply->user->image);
            }
        });
    });

    return response()->json($comments);
}

    public function store(Request $request, $productId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'comment' => 'required|string',
            'rating' => 'nullable|integer|between:1,5', // Đánh giá từ 1 đến 5
        ]);

        // Tạo bình luận mới
        $comment = Comment::create([
            'product_id' => $productId,
            'user_id' => $validated['user_id'],
            'comment' => $validated['comment'],
            'rating' => $validated['rating'] ?? null,
        ]);

        return response()->json($comment, 201);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Xóa bình luận (soft delete)
        $comment->delete();

        return response()->json(['message' => 'Bình luận đã bị xóa'], 200);
    }
}
