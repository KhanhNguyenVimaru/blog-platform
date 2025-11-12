<?php

namespace App\Http\Controllers;

use App\Models\followUser;
use App\Http\Requests\StorefollowUserRequest;
use App\Http\Requests\UpdatefollowUserRequest;
use App\Models\User;
use App\Models\followRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FollowService;
use App\Helpers\ApiResponse;

class FollowUserController extends Controller
{
    protected $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }


    public function banUser($id)
    {
        try {
            $this->followService->banUser($id);
            return redirect()->back()->with('success', 'User banned successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function acceptRequest(StorefollowUserRequest $request)
    {
        try {
            $this->followService->acceptRequest($request);
            return ApiResponse::success(null, 'Follow request accepted successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function denyRequest(Request $request)
    {
        try {
            followRequest::where('followedId', Auth::id())->where('userId_request', $request->send_from_id)->delete();
            Notify::where('id', $request->id)->delete(); // id này là id của notification
            return ApiResponse::success(null, 'Follow request denied successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function deleteFollow($id)
    {
        try {
            followUser::where('authorId', $id)->where('followerId', Auth::id())->delete(); //id này là của đối tượng bị unfollow
            return ApiResponse::success(null, 'Unfollowed successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function followUser($target_id) // truyền vào id của đối tượng hướng tới
    {
        $exists = followUser::where('authorId', $target_id)->where('followerId', Auth::id())->exists();
        $author_privacy = User::where('id', $target_id)->value('privacy');

        if (Auth::id() == $target_id) {
            return ApiResponse::error('You cannot follow yourself.', 400);
        }

        if ($exists) {
            return ApiResponse::error('You are already following this user.', 400);
        }

        try{
            $this->followService->setFollow(Auth::id(), $target_id, $author_privacy);
            return ApiResponse::success(null, 'Follow user completed!');
        } catch(\Exception $e){
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function revokeFollowRequest($id)
    {
        try {
            followRequest::where('followedId', $id)->where('userId_request', Auth::id())->delete();
            return ApiResponse::success(null, 'Follow request revoked successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }
}
