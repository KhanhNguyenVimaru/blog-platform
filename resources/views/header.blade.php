@php
    $user = Auth::user();
    $role = $user ? $user->role : null;
@endphp
<header class="bg-white z-50"
    data-user-profile-url="{{ url('/user-profile') }}/"
    data-post-url="{{ url('/post-content-viewer') }}/"
    data-auth-id="{{ $user->id ?? '' }}">
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
<script src="{{ asset('js/header.js') }}" defer></script>


