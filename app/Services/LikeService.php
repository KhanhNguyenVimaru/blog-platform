<?php

namespace App\Services;

use App\Models\like;
use Illuminate\Support\Facades\DB;

class LikeService
{
    public function likePost($post_id, $user_id, $is_liked)
    {
        if($is_liked) {
            Like::updateOrCreate(
            ['post_id' => $post_id, 'user_id' => $user_id],
            ['like' => true]
        );
        } else {
           Like::where('post_id', $post_id)->where('user_id', $user_id)->delete();
        }
    }
    public function dislikePost($post_id, $user_id, $is_disliked)
    {
        if($is_disliked) {
            Like::updateOrCreate(
            ['post_id' => $post_id, 'user_id' => $user_id],
            ['like' => false]
        );
        } else {
           Like::where('post_id', $post_id)->where('user_id', $user_id)->delete();
        }
    }
}
