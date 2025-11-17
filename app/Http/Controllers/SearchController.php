<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\post;
use App\Models\User;
use App\Models\category;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        $posts = post::searchSuggest($query);
        $users = User::searchSuggest($query);
        $categories = category::searchSuggest($query);

        return view('search', compact('query', 'posts', 'users', 'categories'));
    }
}
