<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Comment;
use App\Models\like;
use App\Models\category;
use App\Models\long_content;

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
    public function comment(){
        return $this->hasMany(Comment::class, 'post_id');
    }
    public function likes(){
        return $this->hasMany(like::class, 'post_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'authorId');
    }

    public static function authorPosts($author_id, $includePrivate = false){
        $query = post::where('authorId', $author_id)
            ->with(['category', 'long_content'])
            ->orderBy('created_at', 'desc');

        if (!$includePrivate) {
            $query->where('status', 'public');
        }
        return $query->get();
    }
}
