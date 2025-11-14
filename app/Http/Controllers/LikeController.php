<?php

namespace App\Http\Controllers;

use App\Models\like;
use App\Models\post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LikeService;

class LikeController extends Controller
{
    protected $likeService;
    public function __construct()
    {
        $this->likeService = new LikeService();
    }

    public function countLike($id)
    {
        try {
            $likeCount = like::where('post_id', $id)->where('like', true)->count();
            $dislikeCount = like::where('post_id', $id)->where('like', false)->count();
            return response()->json(['success' => true, 'likeCount' => $likeCount, 'dislikeCount' => $dislikeCount]);
        } catch (\Throwable $e) {
            Log::error('Count Like Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Count Like Fail']);
        }
    }

    public function like($post_id)
    {
        $is_liked = like::where([['post_id', '=', $post_id],['user_id', "=", Auth::id()],['like', "=", true]])->first();
        try{
            $this->likeService->likePost($post_id, Auth::id(), $is_liked ? false : true);
            return response()->json(['success' => true, 'message' => 'Liked successfully', 'status' => $is_liked ? false : true]);
        } catch (\Throwable $e) {
            Log::error('Like Post Failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Like Post Fail']);
        }
    }

    public function dislike($post_id)
    {
        $is_disliked = like::where([['post_id', '=', $post_id],['user_id', "=", Auth::id()],['like', "=", false]])->first();
        try{
            $this->likeService->dislikePost($post_id, Auth::id(), $is_disliked ? false : true);
            return response()->json(['success' => true, 'message' => 'Disliked successfully', 'status' => $is_disliked ? false : true]);
        } catch (\Throwable $e) {
            Log::error('Dislike Post Failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Dislike Post Fail']);
        }
    }
}
