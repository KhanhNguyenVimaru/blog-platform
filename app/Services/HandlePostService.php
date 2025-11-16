<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\post;
use App\Models\long_content;
use App\Models\Notify;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class HandlePostService
{
    public function changeStatus($post_id)
    {
        post::where('id', $post_id)
            ->where('authorId', Auth::id())
            ->update(['status' => DB::raw("IF(status='public', 'private', 'public')")]);
    }

    public function createNewPost(array $validated, $request)
    {
        DB::beginTransaction();

        try {
            $exists = Post::where('title', $validated['title'])
                ->where('authorId', Auth::id())
                ->exists();

            if ($exists) {
                throw new \Exception("Post with this title already exists");
            }

            if ($request->hasFile('coverImage')) {
                $path = $request->file('coverImage')->store('uploads', 'public');
                $validated['additionFile'] = Storage::url($path);
            }

            $content = $validated['content'];
            $content_size = strlen($content);

            $postData = [
                'title' => $validated['title'],
                'authorId' => Auth::id(),
                'status' => $validated['status'],
                'categoryId' => $validated['categoryId'] ?? null,
                'groupId' => $validated['groupId'] ?? null,
                'additionFile' => $validated['additionFile'] ?? null,
            ];

            $post = post::create($postData);

            if ($content_size <= 65535) {
                $post->content = $content;
                $post->save();
            } else {
                long_content::create([
                    'postId' => $post->id,
                    'content' => $content,
                ]);
            }

            DB::commit();
            return $post;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function notifyNewPost($post)
    {
        $followers = Auth::user()->followers;
        foreach ($followers as $follower) {
            Notify::create([
                'send_from_id' => Auth::id(),
                'send_to_id' => $follower->id,
                'type' => 'new_post',
                'notify_content' => Auth::user()->name . " has created a new post",
                'addition' => $post->id,
            ]);
        }
    }

    public static function addPostPreview($posts)
    {
        $posts->getCollection()->transform(function ($post) {
            $preview = '';
            $contentArr = json_decode($post->content, true);

            if (isset($contentArr['blocks'])) {
                foreach ($contentArr['blocks'] as $block) {
                    if (isset($block['data']['text'])) {
                        $preview .= strip_tags($block['data']['text']) . ' ';
                    }
                }
            }

            $post->preview = Str::limit(trim($preview), 180);
            return $post;
        });
    }
}
