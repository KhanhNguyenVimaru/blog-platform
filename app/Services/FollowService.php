<?php

namespace App\Services;

use App\Models\FollowRequest;
use App\Models\Notify;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\followUser;

class FollowService
{
    public function acceptRequest($request)
    {
        DB::transaction(function () use ($request) {
            FollowUser::create($request->validated());
            FollowRequest::where('followedId', Auth::id())->where('userId_request', $request->followerId)->delete();
            Notify::create([
                'send_from_id'   => Auth::id(),
                'send_to_id'     => $request->followerId,
                'type'           => 'accepted',
                'notify_content' => sprintf('%s has accepted your request', User::where('id', $request->followerId)->value('name')),
            ]);
        });
    }

    public function banUser($ban_target_id)
    {
        DB::transaction(function () use ($ban_target_id) {
            followUser::updateOrCreate(
                ['authorId' => $ban_target_id, 'followerId' => Auth::id()],
                ['banned' => true]
            );
        });
    }

    public function setFollow(int $myId, int $targetId, string $targetPrivacy): void
    {
        DB::transaction(function () use ($myId, $targetId, $targetPrivacy) {
            $userName = User::whereKey($myId)->value('name');

            if ($targetPrivacy === 'private') {
                FollowRequest::create(['userId_request' => $myId, 'followedId' => $targetId]);
                Notify::create([
                    'send_from_id'   => $myId,
                    'send_to_id'     => $targetId,
                    'type'           => 'follow_request',
                    'notify_content' => sprintf('%s has sent you a follow request', $userName),
                ]);
            } else {
                FollowUser::create(['authorId' => $targetId, 'followerId' => $myId]);
                Notify::create([
                    'send_from_id'   => $myId,
                    'send_to_id'     => $targetId,
                    'type'           => 'follow',
                    'notify_content' => sprintf('%s is following you', $userName),
                ]);
            }
        });
    }
}
