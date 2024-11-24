<?php

namespace App\Http\Controllers\API;
use App\Models\Comment;
use App\Models\ReplyComment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ReplyCommentController extends Controller
{
    public function index($id)
    {
        // Tìm bình luận gốc
        $comment = Comment::findOrFail($id);

        // Lấy tất cả reply của bình luận này
        $replies = $comment->replyComments()->with('user:id,name,image')->get();

        // Trả về kết quả dưới dạng JSON
        return response()->json($replies);
    }
    public function store(Request $request, $id)
{
    // Xác thực dữ liệu
    $validated = $request->validate([
        'reply' => 'required|string', // Xác thực nội dung reply
        'user_id' => 'required|exists:users,id', // Kiểm tra user_id tồn tại trong bảng users
    ]);

    // Lấy user_id từ request
    $user_id = $validated['user_id'];

    // Tạo mới reply comment
    $replyComment = ReplyComment::create([
        'user_id' => $user_id,
        'comment_id' => $id,
        'reply' => $validated['reply'],
    ]);

    return response()->json($replyComment, 201);
}

    
public function update(Request $request, $id)
{
    // Xác thực dữ liệu đầu vào
    $validated = $request->validate([
        'reply' => 'required|string', // Xác thực nội dung reply
        'user_id' => 'required|exists:users,id', // Kiểm tra user_id tồn tại trong bảng users
    ]);

    // Lấy user_id từ request
    $user_id = $validated['user_id'];

    // Tìm reply cần cập nhật
    $reply = ReplyComment::findOrFail($id);

    // Kiểm tra quyền sở hữu reply
    if ($reply->user_id !== $user_id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Cập nhật reply
    $reply->reply = $validated['reply'];
    $reply->save();

    // Trả về reply đã cập nhật
    return response()->json($reply);
}

    // Xóa một reply
    public function destroy(Request $request, $id)
    {
        // Xác thực user_id trong request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Kiểm tra user_id tồn tại trong bảng users
        ]);
    
        // Lấy user_id từ request
        $user_id = $validated['user_id'];
    
        // Tìm reply cần xóa
        $reply = ReplyComment::findOrFail($id);
    
        // Kiểm tra quyền sở hữu reply
        if ($reply->user_id !== $user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Xóa reply
        $reply->delete();
    
        // Trả về thông báo thành công
        return response()->json(['message' => 'Reply deleted successfully']);
    }
    
}
