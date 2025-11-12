@php
    $user = Auth::user();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Account - Blog</title>
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

<body class="bg-gray-100 min-h-screen">
    @include('header')
    <div style="min-height: 80vh;">
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
                        <div class="flex flex-row items-center gap-2 mb-1">
                            <h2 class="text-base font-bold text-gray-800 truncate">{{ $user->name }}</h2>
                            @if ($user->privacy === 'public')
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700 cursor-pointer">{{ $user->privacy }}</span>
                            @else
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-gray-200 text-gray-700 cursor-pointer">{{ $user->privacy }}</span>
                            @endif
                            <a href="{{ route('account') }}"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-2 py-0.5 rounded transition text-xs">Edit</a>
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
                <div class="w-full max-w-7xl mx-auto flex flex-col gap-6 mt-2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="posts-row-all"></div>
                </div>
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
                        @if ($followers->isEmpty())
                            <div class="text-gray-500 text-lg">No followers yet.</div>
                        @else
                            @foreach ($followers as $item)
                                @php $f = $item->follower; @endphp
                                <a href="{{ route('userProfile', ['id' => $f->id]) }}"
                                    class="flex items-center gap-4 p-4 hover:bg-gray-50 transition rounded cursor-pointer">
                                    <img src="{{ $f->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg' }}"
                                        class="w-14 h-14 rounded-full object-cover border" alt="avatar">
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
                        @if ($following->isEmpty())
                            <div class="text-gray-500 text-lg">No following yet.</div>
                        @else
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
                                In contrast, users with <span class="font-semibold text-gray-900">Public</span> accounts
                                can
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
        <div class="w-full h-20"></div>

        <!-- Spinner loading khi xóa -->
        <div id="delete-spinner"
            style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999;background:rgba(255,255,255,0.7);justify-content:center;align-items:center;">
            <div class="spinner"
                style="border: 6px solid #f3f3f3; border-top: 6px solid #2563eb; border-radius: 50%; width: 60px; height: 60px; animation: spin 1s linear infinite;">
            </div>
        </div>
    </div>
    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <style>
        .dropdown-menu {
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            const panel = document.getElementById(id + '-panel');
            if (!modal) return;
            modal.classList.remove('pointer-events-none', 'opacity-0');
            modal.classList.add('opacity-100');
            setTimeout(() => {
                if (panel) {
                    panel.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
                    panel.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                }
            }, 10);
            modal.addEventListener('mousedown', function handler(e) {
                if (panel && !panel.contains(e.target)) {
                    closeModal(id);
                    modal.removeEventListener('mousedown', handler);
                }
            });
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            const panel = document.getElementById(id + '-panel');
            panel.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            panel.classList.add('opacity-0', 'scale-95', 'translate-y-4');
            setTimeout(() => {
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 200);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('followers-link').addEventListener('click', function(e) {
                e.preventDefault();
                openModal('modal-followers');
                // Fetch followers
                // Xoá đoạn fetch API followers/following trong JS
            });
            document.getElementById('following-link').addEventListener('click', function(e) {
                e.preventDefault();
                openModal('modal-following');
                // Fetch following
                // Xoá đoạn fetch API followers/following trong JS
            });
            var privacyBadge = document.querySelector('.bg-indigo-100.text-indigo-700, .bg-gray-200.text-gray-700');
            if (privacyBadge) {
                privacyBadge.style.cursor = 'pointer';
                privacyBadge.addEventListener('click', function(e) {
                    openModal('modal-privacy');
                });
            }

            // Lấy bài viết của user
            fetch('/content-of-users', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(posts => {
                    // Hiển thị số lượng post lên mục Posts (nếu còn dùng ở nơi khác)
                    const postsCount = posts.length;
                    const postsCountEls = document.querySelectorAll('.js-posts-count');
                    postsCountEls.forEach(el => el.textContent = postsCount);
                    const postsList = document.getElementById('posts-row-all');
                    postsList.innerHTML = '';
                    if (!posts.length) {
                        postsList.innerHTML = '<div class="text-gray-500">No posts yet.</div>';
                        return;
                    }
                    posts.forEach(post => {
                        let categoryName = post.category ? post.category.content : 'No category';
                        let status = post.status.charAt(0).toUpperCase() + post.status.slice(1);
                        let createdAt = new Date(post.created_at).toLocaleString();
                        let coverImg = post.additionFile ? post.additionFile :
                            '/images/free-images-for-blog.png';
                        const postDiv = document.createElement('div');
                        postDiv.className =
                            'bg-white rounded-lg shadow p-4 pr-2 flex flex-row gap-4 items-center mb-4 hover:shadow-lg transition min-h-[120px]';
                        postDiv.innerHTML = `
                            <div class="flex items-start gap-4 relative">
                            <img src="${coverImg}" alt="Post Image" class="w-20 h-20 object-cover rounded-md bg-gray-100" onerror="this.src='/images/free-images-for-blog.png'">
                            <div class="flex-1 min-w-0">
                                <a href="/post-content-viewer/${post.id}" class="font-bold text-base text-black cursor-pointer post-title-hover hover:text-blue-600 hover:underline-0 line-clamp-2 h-[48px]" style="text-decoration: none;">${post.title || 'load title failed'}</a>
                                <div class="text-gray-600 text-sm mt-1 ">${post.preview || ''}</div>
                                <div class="flex flex-row items-center gap-2 mt-2">
                                    <span class="inline-block truncate px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold cursor-pointer" style="max-width: 60px">${categoryName}</span>
                                    <span class="px-2 py-1 rounded text-xs ${post.status === 'public' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'} cursor-pointer">${status}</span>
                                    <span class="text-xs text-gray-400 ml-auto">${createdAt}</span>
                                </div>
                            </div>
                            <div class="relative">
                                <button class="dropdown-toggle text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleDropdown(${post.id})">
                                    <svg class="w-5 h-full cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                                    </svg>
                                </button>
                                <div id="dropdown-${post.id}" class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <button onclick="updateStatus(${post.id}, '${post.status === 'public' ? 'private' : 'public'}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Set ${post.status === 'public' ? 'Private' : 'Public'}
                                    </button>
                                    <button onclick="deletePost(${post.id})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        `;
                        postsList.appendChild(postDiv);
                    });
                });

        });

        function toggleDropdown(postId) {
            const dropdown = document.getElementById(`dropdown-${postId}`);
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.getElementsByClassName('dropdown-menu');
            const toggles = document.getElementsByClassName('dropdown-toggle');
            let isToggle = Array.from(toggles).some(toggle => toggle.contains(event.target));
            if (!isToggle) {
                for (let dropdown of dropdowns) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        async function updateStatus(postId, status) {
            try {
                const response = await fetch(`/update-status/${postId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: status
                    })
                });
                if (response.ok) {
                    location.reload(); // Refresh to reflect status change
                } else {
                    alert('Failed to update status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }

        async function deletePost(postId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'This post will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/delete-post/${postId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The post has been deleted successfully.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Failed', 'Could not delete the post.', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8/dist/editorjs.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('footer')
</body>

</html>
