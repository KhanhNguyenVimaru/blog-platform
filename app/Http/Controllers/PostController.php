<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\post;
use App\Http\Requests\StorepostRequest;
use App\Http\Requests\UpdatepostRequest;
use Illuminate\Http\Request;
use App\Models\long_content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\comment;
use App\Models\like;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\category;
use App\Models\followUser;
use App\Models\User;
use App\Models\Notify;
use App\Http\Requests\StoreFileRequest;
use App\Services\HandlePostService;
use Symfony\Component\HttpKernel\HttpCache\Store;

class PostController extends Controller
{
    protected $handlePostService;

    public function __construct()
    {
        $this->handlePostService = new HandlePostService();
    }

    public function categoryPosts($categoryId, $sortBy = 'latest')
    {
        $category = Category::findOrFail($categoryId);

        if (Auth::check()) {
            $query = Post::with(['category', 'author'])
                ->withCount(['likes', 'comment'])
                ->where('status', 'public')
                ->where('categoryId', $categoryId)
                ->whereHas('author', function ($query) {
                    $query->where('privacy', 'public');
                })
                ->where(function ($sub) {
                    $sub->where(function ($banCheck) {
                        // Chặn follower bị banned
                        $banCheck->whereDoesntHave('author.followers', function ($q) {
                            $q->where('followerId', Auth::id())
                                ->where('banned', true);
                        })
                            // Chặn following bị banned
                            ->whereDoesntHave('author.following', function ($q) {
                                $q->where('authorId', Auth::id())
                                    ->where('banned', true);
                            });
                    })
                        // Hoặc vẫn cho phép xem bài của chính mình
                        ->orWhere('authorId', Auth::id());
                });
        } else {
            $query = Post::with(['category', 'author'])
                ->withCount(['likes', 'comment'])
                ->where('status', 'public')
                ->where('categoryId', $categoryId)
                ->whereHas('author', function ($query) {
                    $query->where('privacy', 'public');
                });
        }

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

        // Fetch paginated posts
        $posts = $query->paginate(5, ['*'], 'show-page')->appends(['filter' => $sortBy]);

        // Transform posts to include preview
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

        return response()->json([
            'check_category' => $categoryId,
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'posts' => $posts
        ]);
    }

    public function categoryPage($id)
    {
        $pageId = $id;
        $allCategory = Category::all();
        $bestAuthors = User::select('users.*')
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

        $category = Category::findOrFail($id);

        // Fetch the first public post for the header image
        $firstPost = Post::where('categoryId', $id)
            ->where('status', 'public')
            ->whereHas('author', function ($query) {
                $query->where('privacy', 'public');
            })
            ->first();

        return view('categoryPage', compact('category', 'bestAuthors', 'allCategory', 'firstPost', 'pageId'));
    }

    public function allPosts($sortBy = 'latest')
    {
        if (Auth::check()) {
            $query = Post::with(['category', 'author'])
                ->withCount(['likes', 'comment'])
                ->where('status', 'public')
                ->whereHas('author', function ($query) {
                    $query->where('privacy', 'public');
                })
                ->where(function ($sub) {
                    $sub->where(function ($banCheck) {
                        // Chặn follower bị banned
                        $banCheck->whereDoesntHave('author.followers', function ($q) {
                            $q->where('followerId', Auth::id())
                                ->where('banned', true);
                        })
                            // Chặn following bị banned
                            ->whereDoesntHave('author.following', function ($q) {
                                $q->where('authorId', Auth::id())
                                    ->where('banned', true);
                            });
                    })
                        // Hoặc vẫn cho phép xem bài của chính mình
                        ->orWhere('authorId', Auth::id());
                });
        } else {
            $query = Post::with(['category', 'author'])
                ->withCount(['likes', 'comment'])
                ->where('status', 'public')
                ->whereHas('author', function ($query) {
                    $query->where('privacy', 'public');
                });
        }

        // Apply sorting based on filter
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

        $allPosts = $query->paginate(5, ['*'], 'show-page')->appends(['filter' => $sortBy]);

        // Tạo preview
        $allPosts->getCollection()->transform(function ($post) {
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

        return response()->json([
            'posts' => $allPosts,
            'success' => true,
            'message' => 'Posts retrieved successfully'
        ]);
    }

    public function homePosts()
    {
        $bestAuthors = User::select('users.*')
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

        $allCategory = Category::all();

        // Query trong 7 ngày gần nhất
        $topLikedPosts = Post::withCount(['likes' => function ($query) {
            $query->where('like', true);
        }])
            ->with('category')
            ->whereBetween('created_at', [
                Carbon::now()->subDays(7)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->where('status', 'public')
                        ->whereHas('author', function ($query) {
                            $query->where('privacy', 'public');
                        })
                        ->whereDoesntHave('author.followers', function ($query) {
                            $query->where('followerId', Auth::id())
                                ->where('banned', true);
                        })
                        ->whereDoesntHave('author.following', function ($query) {
                            $query->where('authorId', Auth::id())
                                ->where('banned', true);
                        });
                })
                    ->orWhere('authorId', Auth::id());
            })
            ->orderBy('likes_count', 'desc')
            ->limit(4)
            ->get();

        // Nếu 7 ngày không có post nào thì lấy all time
        if ($topLikedPosts->isEmpty()) {
            $topLikedPosts = Post::withCount(['likes' => function ($query) {
                $query->where('like', true);
            }])
                ->with('category')
                ->where(function ($q) {
                    $q->where(function ($query) {
                        $query->where('status', 'public')
                            ->whereHas('author', function ($query) {
                                $query->where('privacy', 'public');
                            })
                            ->whereDoesntHave('author.followers', function ($query) {
                                $query->where('followerId', Auth::id())
                                    ->where('banned', true);
                            })
                            ->whereDoesntHave('author.following', function ($query) {
                                $query->where('authorId', Auth::id())
                                    ->where('banned', true);
                            });
                    })
                        ->orWhere('authorId', Auth::id());
                })
                ->orderBy('likes_count', 'desc')
                ->limit(4)
                ->get();
        }

        return view('index', compact('topLikedPosts', 'allCategory', 'bestAuthors'));
    }


    public function loadLink($request)
    {
        $url = $request->input('url') ?? $request->query('url');
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['success' => 0, 'message' => 'Invalid URL']);
        }
        try {
            $html = @file_get_contents($url);
            if (!$html) {
                return response()->json(['success' => 0, 'message' => 'Cannot fetch URL']);
            }
            preg_match('/<title>(.*?)<\\/title>/si', $html, $title);
            preg_match('/<meta name="description" content="(.*?)"/si', $html, $desc);
            preg_match('/<meta property="og:image" content="(.*?)"/si', $html, $img);
            return response()->json([
                'success' => 1,
                'meta' => [
                    'title' => $title[1] ?? $url,
                    'description' => $desc[1] ?? '',
                    'image' => $img[1] ?? '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
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
        $post = post::with('category', 'author')->findOrFail($id);

        $comments = Comment::with('user')
            ->where('post_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $countlike = like::where('post_id', $id)
            ->where('like', true)
            ->count();

        $countdislike = like::where('post_id', $id)
            ->where('like', false)
            ->count();

        $checkliked = like::where('post_id', $id)
            ->where('user_id', Auth::id())
            ->where('like', true)
            ->exists();
        $checkdisliked = like::where('post_id', $id)
            ->where('user_id', Auth::id())
            ->where('like', false)
            ->exists();


        return view('post_content_viewer', [
            'content' => $post->content,
            'title' => $post->title,
            'category' => $post->category ? $post->category->content : null,
            'category_id' => $post->category ? $post->category->id : null,
            'author_avatar' => $post->author && $post->author->avatar ? $post->author->avatar : 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg',
            'author_name' => $post->author ? $post->author->name : 'Unknown',
            'created_at' => $post->created_at->format('Y-m-d') ? $post->created_at->format('Y-m-d') : 'Unknown',
            'comments' => $comments,
            'post_id' => $id,
            'countlike' => $countlike,
            'countdislike' => $countdislike,
            'checkliked' => $checkliked,
            'checkdisliked' => $checkdisliked
        ]);
    }
}
