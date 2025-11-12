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
        $send_from_id = $request->send_from_id;
        $notify_id = $request->id;
        try {
            $remove_request = followRequest::where('followedId', Auth::id())->where('userId_request', $send_from_id)->delete();
            $remove_notify = Notify::where('id', $notify_id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function deleteFollow($id)
    {
        $myId = Auth::user()->id;
        try {
            $deleted = followUser::where('authorId', $id)
                ->where('followerId', $myId)
                ->delete();
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Unfollowed successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'No follow relationship found.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function followUser($id)
    {
        $myId = Auth::user()->id;
        $userId = $id;

        // Không tự follow chính mình
        if ($myId == $userId) {
            return response()->json(['success' => false, 'message' => 'You cannot follow yourself.']);
        }

        // đã follow ai đó từ trước
        $exists = \App\Models\followUser::where('authorId', $userId)
            ->where('followerId', $myId)
            ->exists();
        if ($exists) {
            return response()->json(['type' => 'following', 'success' => false, 'message' => 'You are already following this user.']);
        }

        $authorPrivacy = User::where('id', $id)->value('privacy');
        if ($authorPrivacy === "private") {
            // Kiểm tra đã gửi request chưa
            $requestExists = followRequest::where('followedId', $userId)
                ->where('userId_request', $myId)
                ->exists();
            if ($requestExists) {
                return response()->json(['type' => 'sent_request', 'success' => false, 'message' => 'Request already sent']);
            }
            try {
                $queue = new followRequest();
                $queue->followedId = $userId;
                $queue->userId_request = $myId;
                $queue->save();

                $notify = new Notify();
                $notify->send_from_id = $myId;
                $notify->send_to_id = $userId;
                $notify->type = 'follow_request';
                $username = User::where('id', $myId)->value('name');
                $notify->notify_content = $username . ' has sent you a follow request';
                $notify->save();

                return response()->json(['type' => 'request', 'success' => true, 'message' => "Request has been sent"]);
            } catch (\Exception $e) {
                return response()->json(['type' => 'request', 'success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            try {
                $relation = new followUser;
                $relation->authorId = $userId;
                $relation->followerId = $myId;
                $relation->save();

                $notify = new Notify();
                $notify->send_from_id = $myId;
                $notify->send_to_id = $userId;
                $notify->type = 'follow';
                $username = User::where('id', $myId)->value('name');
                $notify->notify_content = $username . ' is following you';
                $notify->save();

                return response()->json(['type' => 'follow', 'success' => true, 'message' => "Follow user completed!"]);
            } catch (\Exception $e) {
                return response()->json(['type' => 'follow', 'success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }

    public function revokeFollowRequest($id)
    {
        $myId = Auth::user()->id;
        try {
            $deleted = \App\Models\followRequest::where('followedId', $id)
                ->where('userId_request', $myId)
                ->delete();
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Request revoked successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'No follow request found.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
