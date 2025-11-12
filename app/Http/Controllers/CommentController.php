<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Http\Requests\StorecommentRequest;
use App\Helpers\ApiResponse;

class CommentController extends Controller
{
    /**
     * Store a newly created comment
     */
    public function store(StorecommentRequest $request)
    {
        try {
            $comment = Comment::create([...$request->validated(), 'user_id' => Auth::id(),])->load('user');
            return ApiResponse::success($comment, 'Comment posted successfully!');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to post comment: ' . $e->getMessage());
        }
    }

    /**
     * Delete a comment
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();
            return ApiResponse::success(null, 'Comment deleted successfully!');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete comment: ' . $e->getMessage());
        }
    }
}



