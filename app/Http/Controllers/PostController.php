<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\post;
use App\Http\Requests\StorepostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\comment;
use App\Models\like;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\category;
use App\Models\User;
use App\Http\Requests\StoreFileRequest;
use App\Services\HandlePostService;
use App\Services\LinkPreviewService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $handlePostService;
    protected $linkPreviewService;

    public function __construct()
    {
        $this->handlePostService = new HandlePostService();
        $this->linkPreviewService = new LinkPreviewService();
    }

    public function categoryPosts($categoryId, $sortBy = 'latest')
    {
        $query = post::defaultPostQuery($categoryId);
        if (Auth::check()) {
            $query = post::applyBanFilter($query, Auth::id());
        }
        $query = post::applySorting($query, $sortBy);
        $posts = $query->paginate(5, ['*'], 'show-page')->appends(['filter' => $sortBy]);
        $this->handlePostService->addPostPreview($posts);

        return response()->json(['success' => true, 'message' => 'Posts retrieved successfully', 'posts' => $posts]);
    }

    public function categoryPage(Request $request, $id) // truyền vào id của category
    {
        $sortBy = $request->query('filter', 'latest');
        $allCategory = Category::all();
        $bestAuthors = User::hasMostLikes();
        $category = Category::findOrFail($id);

        $allCategoryPosts = post::defaultPostQuery($id);
        if (Auth::check()) {
            $allCategoryPosts = post::applyBanFilter($allCategoryPosts, Auth::id());
        }
        $allCategoryPosts = post::applySorting($allCategoryPosts, $sortBy);
        $allCategoryPosts = $allCategoryPosts->paginate(5, ['*'], 'show-page')->appends(['filter' => $sortBy]);

        $this->handlePostService->addPostPreview($allCategoryPosts);
        return view('categoryPage', compact('category', 'bestAuthors', 'allCategory', 'allCategoryPosts', 'sortBy'));
    }

    public function allPosts($sortBy = 'latest')
    {
        $query = post::defaultPostQuery();
        if (Auth::check()) {
            $query = post::applyBanFilter($query, Auth::id());
        }
        $query = post::applySorting($query, $sortBy);
        $allPosts = $query->paginate(5, ['*'], 'show-page')->appends(['filter' => $sortBy]);
        $this->handlePostService->addPostPreview($allPosts);


        return response()->json(['posts' => $allPosts,'success' => true,'message' => 'Posts retrieved successfully']);
    }

    public function homePosts()
    {
        $bestAuthors = User::hasMostLikes();
        $allCategory = Category::all();
        $toplikedPosts = post::defaultPostQuery();
        if (Auth::check()) {
            $toplikedPosts = post::applyBanFilter($toplikedPosts, Auth::id());
        }
        $toplikedPosts = post::applySorting($toplikedPosts, 'popular');
        $topLikedPosts = $toplikedPosts->limit(4)->get();

        return view('index', compact('topLikedPosts', 'allCategory', 'bestAuthors'));
    }


    public function loadLink(Request $request)
    {
        $url = $request->input('url') ?? $request->query('url');
        $response = $this->linkPreviewService->loadLink($url);
        return response()->json($response);
    }


    public function updateStatus($id)
    {
        try {
            $this->handlePostService->changeStatus($id);
            return ApiResponse::success(null, 'Update post status successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function deletePost($id)
    {
        try {
            post::destroy($id);
            return ApiResponse::success(null, 'Delete post successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function uploadFile(StoreFileRequest $request)
    {
        $file = $request->file('image');
        $path = $file->store('uploads', 'public');
        return response()->json(['success' => true, 'file' => ['url' => Storage::url($path)]]);
    }

    public function storeContent(StorepostRequest $request)
    {
        try {
            $newpost = $this->handlePostService->createNewPost($request->validated(), $request);
            $this->handlePostService->notifyNewPost($newpost);
            return ApiResponse::success(['postId' => $newpost->id], 'Post created successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function contentOfUsers()
    {
        try {
            $posts = post::authorPosts(Auth::id(), true);
            return ApiResponse::success($posts, 'Author posts retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    public function contentOfAuthor($id) // truyền vào id của author
    {
        try {
            $posts = post::authorPosts($id);
            return ApiResponse::success($posts, 'Author posts retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Xem nội dung post dạng JSON EditorJS
     */
    public function viewContentJson($id)
    {
        $post = Post::with(['category', 'author'])->findOrFail($id);

        $comments = Comment::with('user')->where('post_id', $id)->latest()->get();
        $likes = Like::where('post_id', $id)->get();
        $countlike    = $likes->where('like', true)->count();
        $countdislike = $likes->where('like', false)->count();
        $userLike = $likes->firstWhere('user_id', Auth::id());

        return view('post_content_viewer', [
            'content'        => $post->content,
            'title'          => $post->title,
            'category'       => optional($post->category)->content,
            'category_id'    => optional($post->category)->id,
            'author_avatar'  => optional($post->author)->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg',
            'author_name'    => optional($post->author)->name ?? 'Unknown',
            'created_at'     => $post->created_at->format('Y-m-d'),
            'comments'       => $comments,
            'post_id'        => $id,
            'countlike'      => $countlike,
            'countdislike'   => $countdislike,
            'checkliked'     => $userLike && $userLike->like === true,
            'checkdisliked'  => $userLike && $userLike->like === false,
        ]);
    }
}
