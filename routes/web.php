<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\accessUserProfile;
use App\Models\Notify;
use App\Http\Controllers\FollowUserController;
use App\Http\Controllers\NotifyController;
use App\Models\followUser;
use App\Http\Controllers\SearchController;
use App\Console\Commands\DeleteExpiredAccount;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\CommentController;

// PAGE UI
Route::get('/',[PostController::class, 'homePosts'])->name('homePosts');
Route::get('/page_login', function () {return view('login');})->name('login');
Route::get('/page_signup', function () {return view('signup');})->name('signup');
Route::get('/page_account', function () {return view('account');})->name('account')->middleware('auth');
Route::get('/signup-success', function () { return view('signup_success'); });
Route::get('/my-profile', [UserController::class, 'myProfile'])->name('myProfile')->middleware('auth');
Route::get('/writing', function () { return view('writing');})->name('writing')->middleware('auth');
Route::get('/post-content-viewer/{id}', [PostController::class, 'viewContentJson'])->name('post.content.viewer');
Route::get('/user-profile/{id}', [UserController::class, 'userProfile'])->name('userProfile')->middleware(accessUserProfile::class);// User profile
Route::get('/loadUserNotify',[NotifyController::class, 'loadUserNotify'])->name('loadUserNotify')->middleware('auth');
Route::get('/category/{id}', [PostController::class, 'categoryPage'])->name('categoryPage');
Route::get('/notify/account-not-existed', function () {return view('notify.accountNotExisted');})->name('notify.accountNotExisted');
Route::get('/about', function () {return view('about');})->name('about');
// LOGIN/OUT HANDLE
Route::post('/handle_login', [AuthController::class, 'login']);
Route::post('/handle_signup', [AuthController::class, 'signup'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
// API ACCOUNT
Route::patch('/update_user/{id}', [UserController::class, 'updateUserData'])->name('updateUserData')->middleware('auth');
Route::delete('/delete_account', [UserController::class, 'deleteUserAccount'])->name('deleteUserAccount')->middleware('auth');
Route::middleware('auth:api')->post('/change_password', [UserController::class, 'changePassword'])->name('changePassword');
Route::post('/update-avatar', [UserController::class, 'updateAvatar'])->middleware('auth');
Route::get('/ban-user/{id}', [FollowUserController::class, 'banUser'])->name('banUser')->middleware('auth');
Schedule::command(DeleteExpiredAccount::class)->daily();
// API POST
Route::post('/insert-post', [PostController::class, 'storeContent'])->name('insertPost')->middleware('auth');
Route::post('/uploadFile', [PostController::class, 'uploadFile'])->name('uploadFile')->middleware('auth');
Route::get('/content-of-users', [PostController::class, 'contentOfUsers'])->name('contentOfUsers')->middleware('auth'); //  content of users ám chỉ bài viết của người dùng đăng nhập
Route::delete('/delete-post/{id}', [PostController::class, 'deletePost'])->name('deletePost')->middleware('auth');
Route::patch('/update-status/{id}', [PostController::class, 'updateStatus'])->name('updateStatus')->middleware('auth');
Route::get('/categories', [CategoryController::class, 'getAllCategories'])->name('getAllCategories')->middleware('auth'); // API CATEGORY
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');// URL verify register
Route::get('/search-suggest', [UserController::class, 'searchSuggest'])->name('search.suggest');// suggest search
Route::get('/content-of-author/{id}', [PostController::class, 'contentOfAuthor'])->name('contentOfAuthor'); //  content of author là bài viết của người dùng khác
Route::match(['get', 'post'], '/fetchUrl',[PostController::class, 'loadLink'])->name('fetchUrl')->middleware('auth');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/posts/{sortBy?}', [PostController::class, 'allPosts']);
Route::get('/api/category/{categoryId}/posts/{sortBy?}', [PostController::class, 'categoryPosts']);
// COMMENT ROUTES
Route::post('/comments', [CommentController::class, 'store'])->name('comments.store')->middleware('auth');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy')->middleware('auth');
// LIKE/DISLIKE ROUTES
Route::post('/like', [App\Http\Controllers\LikeController::class, 'like'])->name('like')->middleware('auth');
Route::post('/dislike', [App\Http\Controllers\LikeController::class, 'dislike'])->name('dislike')->middleware('auth');
Route::get('/count-like/{id}', [App\Http\Controllers\LikeController::class, 'countLike'])->name('countLike');
// FOLLOW/UNFOLLOW USER
Route::get('/follow_user/{id}',[FollowUserController::class, 'followUser'])->name('followUser')->middleware('auth');
Route::delete('/delete_follow/{id}', [FollowUserController::class, 'deleteFollow'])->name('deleteFollow')->middleware('auth');
Route::delete('/revoke_follow_request/{id}', [FollowUserController::class, 'revokeFollowRequest'])->name('revokeFollowRequest');
Route::get('/my-followers', [UserController::class, 'getFollowers'])->middleware('auth'); // trả về số người follow
Route::get('/my-following', [UserController::class, 'getFollowing'])->middleware('auth'); // trả về số người mình đang follow
Route::delete('/deny-request',[FollowUserController::class, 'denyRequest'])->middleware('auth');
Route::post('/accept-request',[FollowUserController::class, 'acceptRequest'])->middleware('auth');
Route::delete('/delete-notify',[NotifyController::class, 'deleteNotify'])->middleware('auth');
Route::delete('/delete-ban/{id}', [UserController::class, 'deleteBan'])->middleware('auth'); // trả về số người bị cấm

