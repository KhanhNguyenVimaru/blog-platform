<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>User Profile - Blog</title>
    <link rel="icon" type="image/x-icon" href="https://www.svgrepo.com/show/475713/blog.svg" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .editorjs-content p {
            font-size: 1.08rem;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        .editorjs-content h1,
        .editorjs-content h2,
        .editorjs-content h3 {
            font-size: 1.35rem;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .editorjs-content img {
            max-height: 120px;
            object-fit: cover;
        }

        .editorjs-content ul,
        .editorjs-content ol {
            font-size: 0.9rem;
            margin-left: 1rem;
        }

        .post-title-hover {
            transition: color 0.2s;
            cursor: pointer;
        }

        .post-title-hover:hover {
            color: #2563eb;
            text-decoration: none !important;
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-gray-100" style = "min-height: 80vh" data-profile-user-id="{{ $user->id }}">
    @include('header')
    @if (session('success'))
        <div id="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div id="alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div style="min-height: 100vh;">
        @include('components.breadcrumb', [
            'links' => \App\Http\Controllers\Controller::generateBreadcrumbLinks(),
        ])
        <div class="w-full max-w-7xl mx-auto flex flex-col gap-6 mt-2">
            <div class="flex flex-col items-start w-full mt-0 pt-0">
                <div class="mt-2 p-4 rounded-lg flex flex-row items-center gap-6 mb-8 mx-0 pb-8"
                    style="width: calc(33.333% - 1rem);">
                    <!-- Avatar bên trái -->
                    <div class="flex-shrink-0 flex items-center justify-center w-fit h-24">
                        <img src="{{ $user->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg' }}"
                            class="w-20 h-20 rounded-full object-cover border border-gray-300 ml-5  " alt="Avatar">
                    </div>
                    <!-- Thông tin bên phải, content fit, căn phải -->
                    <div class="flex flex-col items-start justify-center flex-1 pl-4">
                        <div class="flex flex-row items-center gap-2">
                            <h2 class="text-base font-bold text-gray-800 truncate">{{ $user->name }}</h2>
                            @if (Auth::id() !== $user->id)
                                @if ($already_followed)
                                    <button id="unfollow-btn"
                                        class="bg-gray-400 cursor-pointer text-white font-semibold px-4 py-1 rounded-full shadow transition text-xs mx-2">Following</button>
                                @elseif($request_sent)
                                    <button id="revoke-request-btn"
                                        class="bg-gray-400 cursor-pointer text-white font-semibold px-4 py-1 rounded-full shadow transition text-xs mx-2">Pending</button>
                                @elseif($can_request_again)
                                    <button id="request-btn"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-1 rounded-full shadow transition text-xs mx-2 cursor-pointer">Request</button>
                                @elseif($ban)
                                    <button id="ban-btn"
                                        class="bg-gray-400 cursor-pointer text-white font-semibold px-4 py-1 rounded-full shadow transition text-xs mx-2">Unblock</button>
                                @else
                                    <button id="follow-btn"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-1 rounded-full shadow transition text-xs mx-2 cursor-pointer">Follow</button>
                                @endif
                            @else
                                <a href="{{ route('account') }}"
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-2 py-0.5 rounded transition text-xs">Edit</a>
                            @endif
                            <div class="relative inline-block text-left">
                                <!-- Nút bấm SVG -->
                                <button id="menuButton" class="p-2 rounded hover:bg-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6 cursor-pointer">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                    </svg>
                                </button>

                                <!-- Dropdown menu -->
                                <div id="menuDropdown"
                                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
                                    <a href="{{ route('banUser', ['id' => $user->id]) }}"
                                        class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Ban this user</a>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 truncate mb-1">{{ $user->email }}</span>
                        <div class="flex flex-row gap-6 mt-1">
                            <a href="#" id="followers-link" class="text-center">
                                <div class="text-base font-bold text-gray-800">{{ $count_follower ?? 0 }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Followers</div>
                            </a>
                            <a href="#" id="following-link" class="text-center">
                                <div class="text-base font-bold text-gray-800">{{ $count_following ?? 0 }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Following</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Grid for Posts bên dưới -->
                @if (request('private_profile'))
                    <div id="private-notice"
                        class="w-full text-gray-700 p-8 rounded-lg text-start text-base font-semibold mt-6 min-h-[180px] mt-4">
                        This account is <span class="font-bold mx-1">private</span>. <br>
                        You need to follow to see this user's posts.
                    </div>
                @elseif($ban)
                    <div
                        class="w-full text-gray-500 p-8 rounded-lg text-start text-base font-semibold mt-6 min-h-[180px] mt-4">
                        This account is <span class="font-bold mx-1">private</span>.
                        You need to follow this user to see their posts. <br>
                        Unblock them and send a follow request to gain access.
                    </div>
                @else
                    <div class="w-full max-w-7xl mx-auto flex flex-col gap-6 mt-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="posts-row-all"></div>
                    </div>
                @endif
            </div>
        </div>


        <!-- Modal Followers -->
        <div id="modal-followers"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75 transition-opacity duration-300 opacity-0 pointer-events-none">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-start sm:p-0 w-full">
                <div id="modal-followers-panel"
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-1/2 sm:max-w-2xl opacity-0 scale-95 translate-y-4 sm:translate-y-0 sm:scale-95 duration-300">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Followers</h3>
                        @if (isset($followers) && $followers->isEmpty())
                            <div class="text-gray-500 text-lg">No followers yet.</div>
                        @elseif(isset($followers))
                            @foreach ($followers as $item)
                                @php $f = $item->follower; @endphp
                                <a href="{{ route('userProfile', ['id' => $f->id]) }}"
                                    class="flex items-center gap-4 p-4 hover:bg-gray-50 transition rounded cursor-pointer">
                                    <img src="{{ $f->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg' }}"
                                        class="w-14 h-14 rounded-full" alt="avatar">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-lg text-gray-800">{{ $f->name }}</span>
                                        <span class="text-base text-gray-500">{{ $f->email }}</span>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 flex justify-end">
                        <button type="button"
                            class="mt-3 inline-flex justify-center rounded-md bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 cursor-pointer"
                            onclick="closeModal('modal-followers')">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Following -->
        <div id="modal-following"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75 transition-opacity duration-300 opacity-0 pointer-events-none">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-start sm:p-0 w-full">
                <div id="modal-following-panel"
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-1/2 sm:max-w-2xl opacity-0 scale-95 translate-y-4 sm:translate-y-0 sm:scale-95 duration-300">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Following</h3>
                        @if (isset($following) && $following->isEmpty())
                            <div class="text-gray-500 text-lg">No following yet.</div>
                        @elseif(isset($following))
                            @foreach ($following as $item)
                                @php $a = $item->following; @endphp
                                <a href="{{ route('userProfile', ['id' => $a->id]) }}"
                                    class="flex items-center gap-4 p-4 hover:bg-gray-50 transition rounded cursor-pointer">
                                    <img src="{{ $a->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg' }}"
                                        class="w-14 h-14 rounded-full" alt="avatar">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-lg text-gray-800">{{ $a->name }}</span>
                                        <span class="text-base text-gray-500">{{ $a->email }}</span>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 flex justify-end">
                        <button type="button"
                            class="mt-3 inline-flex justify-center rounded-md bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 cursor-pointer"
                            onclick="closeModal('modal-following')">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Privacy -->
        <div id="modal-privacy"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75 transition-opacity duration-300 opacity-0 pointer-events-none">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-start sm:p-0 w-full">
                <div id="modal-privacy-panel"
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 scale-95 translate-y-4 sm:translate-y-0 sm:scale-95 duration-300">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Privacy</h3>
                        <!-- Nội dung modal privacy -->
                        <div class="text-gray-700 text-sm leading-relaxed">
                            <p class="mb-4 text-left">
                                Users who set their account privacy to <span
                                    class="font-semibold text-gray-900">Private</span> must approve each follow request
                                manually, meaning other users need to send a follow request and wait for approval.<br>
                                In contrast, users with <span class="font-semibold text-gray-900">Public</span>
                                accounts can
                                be followed instantly without requiring any approval.
                            </p>
                            <span class="text-left block text-sm">Want to reset your privacy? <a href="/page-account"
                                    class="text-indigo-600 font-semibold hover:underline transition">Setting
                                    here</a></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 flex justify-end">
                        <button type="button"
                            class="mt-3 inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 cursor-pointer"
                            onclick="closeModal('modal-privacy')">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách bài viết của user -->
        <div id="user-posts" class="max-w-6xl mx-auto mt-8">
            <div id="posts-list"
                class="w-11/12 md:w-full mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 justify-between">
            </div>
        </div>
        <!-- Spinner loading khi xóa -->
        <div id="delete-spinner"
            style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999;background:rgba(255,255,255,0.7);justify-content:center;align-items:center;">
            <div class="spinner"
                style="border: 6px solid #f3f3f3; border-top: 6px solid #2563eb; border-radius: 50%; width: 60px; height: 60px; animation: spin 1s linear infinite;">
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8/dist/editorjs.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/userProfile.js') }}" defer></script>
    @include('footer')
</body>

</html>
