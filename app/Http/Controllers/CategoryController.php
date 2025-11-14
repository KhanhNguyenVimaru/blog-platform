<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\post;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getAllCategories()
    {
        $categories = category::all();
        return response()->json($categories);
    }
}
