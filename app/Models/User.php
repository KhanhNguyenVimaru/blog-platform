<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\post;
use App\Models\like;
use App\Models\followUser;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_token',
        'description',
        'privacy',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function comment(){
        return $this->hasMany(\App\Models\comment::class, 'post_id');
    }
    public function like(){
        return $this->hasMany(\App\Models\comment::class, 'post_id');
    }
    public function post(){
        return $this->hasMany(User::class, 'authorId');
    }
    public function likesThroughPosts()
    {
        return $this->hasManyThrough(like::class, post::class);
    }
    // public function inRelationships()
    // {
    //     return $this->belongsTo(followUser::class, 'authorId', 'followerId');
    // }


    public function following()
    {
        return $this->belongsToMany(
            User::class,     // Model liên quan (vẫn là User)
            'follow_users',       // Tên bảng pivot
            'followerId',   // FK trong pivot trỏ tới user hiện tại
            'authorId'    // FK trong pivot trỏ tới user được follow
        );
    }

    // Những user đang follow user này
    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follow_users',
            'authorId',   // FK trong pivot trỏ tới user hiện tại
            'followerId'    // FK trong pivot trỏ tới user follower
        );
    }

    public static function hasMostLikes()
    {
        return User::select('users.*')
            ->addSelect([
                'likes_count' => DB::table('likes')
                    ->join('posts', 'posts.id', '=', 'likes.post_id')
                    ->whereColumn('posts.authorId', 'users.id')
                    ->selectRaw('COUNT(*)')
            ])
            ->having('likes_count', '>', 0)
            ->orderByDesc('likes_count')
            ->limit(3)
            ->get();
    }

    public static function searchSuggest($query)
    {
        return User::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->get();
    }
}


