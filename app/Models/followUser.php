<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class followUser extends Model
{
    protected $fillable = [
        'authorId',
        'followerId',
        'banned',
    ];
    public function author()
    {
        return $this->belongsTo(User::class, 'authorId');
    }
    public function follower()
    {
        return $this->belongsTo(User::class, 'followerId');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'authorId');
    }
}
