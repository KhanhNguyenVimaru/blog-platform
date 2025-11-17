<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    public function posts(){
        return $this->hasMany(\App\Models\Post::class, 'categoryId');
    }

    public static function searchSuggest($query){
        return category::where('content', 'like', "%$query%")->get();
    }
}
