<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Storage;
use App\Models\Group;
use App\Models\Post;
use App\Models\followUser;

class UserController extends Controller
{
    public function userProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $private_profile = $request->get('private_profile', false);
        $already_followed = $request->get('already_followed', false);
        $request_sent = $request->get('request_sent', false);
        $can_request_again = $request->get('can_request_again', false);
        $ban = $request->get('ban', false);

        $count_follower = followUser::where('authorId', $user->id)
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->count();

        $count_following = followUser::where('followerId', $user->id)
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->count();

        $followers = followUser::where('authorId', $user->id)->with('follower')
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->get();
        $following = followUser::where('followerId', $user->id)->with('following')
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->get();

        return view('userProfile', compact('user', 'private_profile', 'already_followed', 'request_sent', 'can_request_again', 'count_follower', 'count_following', 'followers', 'following', 'ban',));
    }

    public function myProfile(Request $request)
    {
        $user = Auth::user();
        $count_follower = \App\Models\followUser::where('authorId', $user->id)
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->count();
        $count_following = \App\Models\followUser::where('followerId', $user->id)
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->count();

        $followers = \App\Models\followUser::where('authorId', $user->id)->with('follower')
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->get();
        $following = \App\Models\followUser::where('followerId', $user->id)->with('following')
            ->where(function ($q) {
                $q->where('banned', false)
                    ->orWhereNull('banned');
            })
            ->get();

        return view('myProfile', compact('user', 'count_follower', 'count_following', 'followers', 'following'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.verify.signUpAccount',
            text: 'mail.verify.signUpAccount'
        );
    }
    public function verifySignUp(): Envelope
    {
        return new Envelope(
            from: new Address('khanhnd05@gmail.com', 'Digital Blog administrator'),
            subject: 'Verify your account',
        );
    }

    public function updateUserData(Request $request, string $id)
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'privacy' => 'required|in:public,private',
        ], [
            'description.max' => 'Description cannot exceed 255 characters.',
            'privacy.required' => 'Privacy setting is required.',
            'privacy.in' => 'Privacy must be either public or private.',
        ]);

        try {
            $user = User::findOrFail($id);
            $user->description = $request->description;
            $user->privacy = $request->privacy;
            $user->save();

            return redirect('/page-account')->with('success', 'Account settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update account settings. Please try again.'])->withInput();
        }
    }
    public function deleteBan($id)
    {
        try {
            $deleted = followUser::where('authorId', $id)->where('followerId', Auth::id())->delete();
            return ApiResponse::success('Unbanned successfully', ['deleted' => $deleted]);
        } catch (\Exception $e) {
            return ApiResponse::error('Error unbanning user: ' . $e->getMessage());
        }
    }

    public function deleteUserAccount(Request $request)
    {
        try {
            $user = Auth::user();
            $user->tokens->each->revoke();
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            User::where('id', $user->id)->delete();
            return ApiResponse::success('Account deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting account: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        $user->password = \Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.'
        ], 200);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $user = Auth::user();
        $file = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $filename, 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();
        return response()->json(['success' => true, 'avatar_url' => $user->avatar]);
    }

    public function searchSuggest(Request $request)
    {
        $q = $request->input('q', '');
        if (!$q) return response()->json([]);
        $limit = 10;
        $users = \App\Models\User::where(function ($query) use ($q) {
            $query->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%");
        })
            ->selectRaw("'user' as type, name as value, id")
            ->limit($limit)->get();
        $groups = Group::where('name', 'like', "%$q%")
            ->selectRaw("'group' as type, name as value, id")
            ->limit($limit)->get();
        $posts = Post::where('title', 'like', "%$q%")
            ->selectRaw("'post' as type, title as value, id")
            ->limit($limit)->get();
        $results = $users->concat($groups)->concat($posts)->take($limit);
        return response()->json($results->values());
    }
}
