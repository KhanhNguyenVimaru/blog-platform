<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Comment;
use App\Models\like;
use App\Models\category;
use App\Models\long_content;
use Illuminate\Support\Str;

class post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $fillable = [
        'title',
        'content',
        'additionFile',
        'authorId',
        'groupId',
        'status',
        'categoryId',
    ];

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(category::class, 'categoryId');
    }

    public function long_content()
    {
        return $this->hasMany(long_content::class, 'postId');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'authorId');
    }
    public function comment()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
    public function likes()
    {
        return $this->hasMany(like::class, 'post_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'authorId');
    }

    public static function authorPosts($author_id, $includePrivate = false)
    {
        $query = post::where('authorId', $author_id)
            ->with(['category', 'long_content'])
            ->orderBy('created_at', 'desc');

        if (!$includePrivate) {
            $query->where('status', 'public');
        }
        return $query->get();
    }

    public static function defaultPostQuery($categoryId = null)
    {
        $posts = post::with(['category', 'author'])
            ->withCount(['likes', 'comment'])
            ->where('status', 'public')
            ->whereHas('author', function ($query) {
                $query->where('privacy', 'public');
            });

        if (!is_null($categoryId)) {
            $posts->where('categoryId', $categoryId);
        }
        return $posts;
    }

    public static function applyBanFilter($query, $userId)
    {
        return $query->where(function ($sub) use ($userId) {
            $sub->where(function ($banCheck) use ($userId) {
                $banCheck->whereDoesntHave('author.followers', function ($q) use ($userId) {
                    $q->where('followerId', $userId)
                        ->where('banned', true);
                })
                    ->whereDoesntHave('author.following', function ($q) use ($userId) {
                        $q->where('authorId', $userId)
                            ->where('banned', true);
                    });
            })
                ->orWhere('authorId', $userId);
        });
    }

    public static function applySorting($query, $sortBy)
    {
        switch ($sortBy) {
            case 'popular':
                $query->orderByDesc('likes_count');
                break;
            case 'interaction':
                $query->orderByDesc('comment_count');
                break;
            default:
                $query->orderByDesc('created_at');
                break;
        }
        return $query;
    }

    public static function searchSuggest($query){
        return post::with('author')
            ->where('title', 'like', "%$query%")
            ->where('status', 'public')
            ->whereHas('author', function ($q) {
                $q->where('privacy', 'public');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
