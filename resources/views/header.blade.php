@php
    $user = Auth::user();
    $role = $user ? $user->role : null;
@endphp
<header class="bg-white z-50">
    <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
        <div class="flex flex-1 min-w-0 items-center">
            <a href="/" class="flex items-center gap-2 -m-1.5 p-1.5 cursor-pointer">
                <span class="text-md font-bold" style="color: #2832c2">DIGITAL BLOG</span>
            </a>
        </div>
        <div class="flex flex-1 justify-center min-w-0 relative" id="nav-links">
            <a href="/" class="text-sm font-semibold text-gray-600 hover:text-black px-4">Feed</a>
            <a href="/writing" class="text-sm font-semibold text-gray-600 hover:text-black px-4">Writing</a>
            <a href="#" id="notification-bell"
                class="text-sm font-semibold text-gray-600 hover:text-black px-4 relative">Notification
                <div id="notification-dropdown"
                    class="hidden absolute left-0 mt-2 w-120 bg-white border border-gray-200 rounded shadow-lg z-50 p-4">
                    <div class="font-semibold text-black mb-2">Notifications</div>
                    <div class="text-gray-500 text-sm">No notifications yet.</div>
                </div>
            </a>
            <a href="#" id="search-nav-link"
                class="text-sm font-semibold text-gray-600 hover:text-black px-4">Search</a>
            <a href="/about"
                class="text-sm font-semibold text-gray-600 hover:text-black px-4 whitespace-nowrap">About</a>

        </div>
        <div class="flex flex-1 justify-end min-w-0 items-center gap-3 relative">
            <div id="search-input-wrapper"
                class="fixed left-0 right-0 top-0 z-50 flex justify-center items-center h-[72px]">
                <input id="search-input" type="text" placeholder="Search..."
                    class="w-1/2 min-w-[200px] max-w-xl px-4 py-2 border border-gray-300 rounded-full shadow focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm bg-white" />
            </div>
            @if (!$user)
                <a id="login-link" href="/page-login" class="text-sm font-semibold text-gray-600 hover:text-black">Log
                    in<span aria-hidden="true">&rarr;</span></a>
            @else
                <div id="account-dropdown-wrapper" class="relative">
                    <button id="account-link" type="button"
                        class="flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-black focus:outline-none cursor-pointer">
                        <span>{{ $user->name }}</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="account-dropdown"
                        class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg z-50">
                        <a href="/my-profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">My Profile</a>
                        <a href="/page-account" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Setting</a>
                        @if ($role === 'admin')
                            <a href="/admin" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Admin</a>
                        @endif
                        <button id="logout-btn" type="button"
                            class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 text-red-600 cursor-pointer">Logout</button>
                    </div>
                </div>
            @endif
        </div>
    </nav>
</header>

<style>
    #search-input-wrapper {
        transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-16px) scale(0.98);
    }

    #search-input-wrapper.active {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }
</style>

<script src="/js/logout.js"></script>
<script src="/js/search_suggest.js"></script>
<script>
    window.userProfileUrlBase = "{{ url('/user-profile') }}/";
    window.postAddressBase = "{{ url('/post-content-viewer') }}/";
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginLink = document.getElementById('login-link');
        const accountWrapper = document.getElementById('account-dropdown-wrapper');
        const accountLink = document.getElementById('account-link');
        const accountDropdown = document.getElementById('account-dropdown');
        const mobileWrapper = document.getElementById('mobile-account-wrapper');
        // Hiển thị đúng trạng thái đăng nhập
        if (loginLink && accountWrapper) {
            if (localStorage.getItem('token') === null) {
                loginLink.style.display = 'block';
                accountWrapper.style.display = 'none';
                if (mobileWrapper) mobileWrapper.style.display = 'none';
            } else {
                loginLink.style.display = 'none';
                accountWrapper.style.display = 'block';
                if (mobileWrapper) mobileWrapper.style.display = 'block';
            }
        }
        // Dropdown logic
        if (accountLink && accountDropdown) {
            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function(e) {
                if (!accountDropdown.contains(e.target) && !accountLink.contains(e.target)) {
                    accountDropdown.classList.add('hidden');
                }
            });
            // Toggle dropdown khi click vào account
            accountLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (accountDropdown.classList.contains('hidden')) {
                    accountDropdown.classList.remove('hidden');
                } else {
                    accountDropdown.classList.add('hidden');
                }
            });
        }
        // Search nav-link logic
        const searchNavLink = document.getElementById('search-nav-link');
        const navLinks = document.getElementById('nav-links');
        const searchInputWrapper = document.getElementById('search-input-wrapper');
        const searchInput = document.getElementById('search-input');
        if (searchNavLink && navLinks && searchInputWrapper && searchInput) {
            searchNavLink.addEventListener('click', function(e) {
                e.preventDefault();
                navLinks.classList.add('hidden');
                searchInputWrapper.classList.add('active');
                searchInput.focus();
            });
            // Click ra ngoài input sẽ ẩn input và hiện lại nav-links
            document.addEventListener('mousedown', function(e) {
                if (!searchInputWrapper.contains(e.target) && !searchNavLink.contains(e.target)) {
                    searchInputWrapper.classList.remove('active');
                    navLinks.classList.remove('hidden');
                }
            });
            // Optional: Enter để submit search
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    if (query) {
                        window.location.href = '/search?query=' + encodeURIComponent(query);
                    }
                }
            });
        }
        // Notification bell logic
        const bell = document.getElementById('notification-bell');
        const dropdown = document.getElementById('notification-dropdown');

        if (bell && dropdown) {
            bell.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (dropdown.classList.contains('hidden')) {
                    fetch('/loadUserNotify', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(notifies => {
                            let html =
                                '<div class="font-semibold text-gray-700 mb-2">Notifications</div>';
                            if (!notifies.length) {
                                html +=
                                    '<div class="text-gray-500 text-sm">No notifications yet.</div>';
                            } else {
                                html += notifies.map(n => {
                                    let userSentId = n.send_from_id;
                                    let postId = n.addition || '';
                                    if (n.type === 'follow_request') {
                                        return `<div
                                            class="flex items-center justify-between gap-2 py-2 hover:bg-gray-50 h-[40px]"
                                            onclick="window.location.href='${window.userProfileUrlBase}${userSentId}'">
                                            <span class="text-sm text-gray-600 flex-1">${n.notify_content || 'You have a new follow request.'}</span>
                                            <button class="cursor-pointer hover:bg-blue-300 accept-follow-btn bg-blue-500 text-white px-2 py-1 rounded text-xs mr-1" data-id="${n.id}" onclick="acceptRequest(${n.id}, ${n.send_from_id})">Accept</button>
                                            <button class="cursor-pointer hover:bg-gray-300 reject-follow-btn bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs" data-id="${n.id}" onclick="denyRequest(${n.id}, ${n.send_from_id})">X</button>
                                        </div>`;
                                    } else if (n.type === 'new_post') {
                                        return `<div class="flex items-center justify-between h-[40px] text-sm py-2 px-2 hover:bg-gray-50 text-gray-600">
                                            <span onclick="window.location.href='${window.postAddressBase}${postId}'" class="flex-1 cursor-pointer">${n.notify_content || 'You have a new notification.'}</span>
                                            <button onclick="deleteNotify(${n.id}, event)" class="text-gray-500 hover:text-red-600 cursor-pointer" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>`;
                                    } else{
                                        return `<div class="flex items-center justify-between h-[40px] text-sm py-2 px-2 hover:bg-gray-50 text-gray-600">
                                            <span onclick="window.location.href='${window.userProfileUrlBase}${userSentId}'" class="flex-1 cursor-pointer">${n.notify_content || 'You have a new notification.'}</span>
                                            <button onclick="deleteNotify(${n.id}, event)" class="text-gray-500 hover:text-red-600 cursor-pointer" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>`;
                                    }
                                }).join('');
                            }
                            dropdown.innerHTML = html;

                        });
                }
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
</script>
<script>
    function denyRequest(id, sendFromId) {
        if (!id || !sendFromId) return;
        fetch(`/deny-request`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    send_from_id: sendFromId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Đổi nút X thành span Rejected
                    const btn = document.querySelector(`button.reject-follow-btn[data-id='${id}']`);
                    if (btn) {
                        const span = document.createElement('span');
                        span.className = 'ml-2 text-xs text-gray-400 font-semibold';
                        span.innerText = 'Rejected';
                        btn.replaceWith(span);
                    }
                } else {
                    alert(data.message || 'Error denying request!');
                }
            })
            .catch(() => {
                alert('Error denying request!');
            });
    }
</script>
<script>
    function acceptRequest(id, sendFromId) {
        if (!id || !sendFromId) return;
        fetch(`/accept-request`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    followerId: sendFromId,
                    authorId: {{ Auth::id() }}
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const btn = document.querySelector(`button.accept-follow-btn[data-id='${id}']`);
                    if (btn) {
                        const span = document.createElement('span');
                        span.className = 'ml-2 text-xs text-green-500 font-semibold';
                        span.innerText = 'Accepted';
                        btn.replaceWith(span);
                    }
                    // Gọi fetch xóa notify
                    fetch(`/delete-notify`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id,
                        })
                    });
                } else {
                    alert(data.message || 'Error accepting request!');
                }
            })
            .catch(() => {
                alert('Error accepting request!');
            });
    }
</script>
<script>
    function deleteNotify(id, event) {
        // Ngăn việc click lan ra ngoài gây đóng dropdown
        event.stopPropagation();

        // Lấy phần tử thông báo
        let btn = event.currentTarget;
        let parentDiv = btn.closest('div');

        // Xóa ngay trên giao diện
        if (parentDiv) {
            parentDiv.remove();
        }

        // Gửi request AJAX đến Laravel
        fetch(`/delete-notify`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Xóa thất bại.');
            }
        })
        .catch(err => console.error('Error:', err));
    }
</script>


