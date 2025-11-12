<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class followRequest extends Model
{
    /** @use HasFactory<\Database\Factories\FollowRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'followedId',
        'userId_request',
    ];
}
