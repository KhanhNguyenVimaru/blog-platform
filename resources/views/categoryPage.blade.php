<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Blog - {{ $category->content ?? 'Category' }}</title>
    <link rel="icon" type="image/x-icon" href="https://www.svgrepo.com/show/475713/blog.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .filter-link.text-black {
            font-weight: bold;
            border-bottom: 2px solid black;
        }

        .filter-link {
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>
    @include('header')

    @php
        $firstPost = $allCategoryPosts->first();
        $activeFilter = $sortBy ?? 'latest';
        $baseCategoryUrl = route('categoryPage', ['id' => $category->id]);
        $filterUrl = function (string $filter) use ($baseCategoryUrl) {
            if ($filter === 'latest') {
                return $baseCategoryUrl;
            }
            return $baseCategoryUrl . '?filter=' . $filter;
        };
    @endphp

    <div class="relative w-full h-50 md:h-96 flex items-start justify-center"
        style="background-color: rgb(234, 234, 234);">
        <div class="flex items-center mt-28">
            <img src="{{ $post->additionFile ?? '/images/free-images-for-blog.png' }}" alt="Category Image"
                class="w-32 h-32 object-cover rounded-md bg-gray-100 flex-shrink-0"
                onerror="this.src='/images/free-images-for-blog.png'">
            <h1 class="text-2xl md:text-5xl font-semibold text-gray-800 text-center ml-10"
                style="font-family: bahnschrift; color: rgb(82, 103, 187);">
                {{ strtoupper($category->content ?? 'CATEGORY') }}
            </h1>
        </div>
    </div>

    <div class="w-full mt-14 min-h-[100vh]">
        <div class="max-w-screen-xl mx-auto h-full pb-5">
            <!-- Navigation -->
            <div class="flex justify-start mb-4 gap-8 text-center pl-4" id="nav-links">
                <a href="{{ $filterUrl('latest') }}" data-filter="latest"
                    class="filter-link text-base {{ $activeFilter === 'latest' ? 'text-black' : 'text-gray-500 hover:text-black' }} px-4 cursor-pointer"
                    aria-current="{{ $activeFilter === 'latest' ? 'true' : 'false' }}">LATEST</a>
                <a href="{{ $filterUrl('popular') }}" data-filter="popular"
                    class="filter-link text-base {{ $activeFilter === 'popular' ? 'text-black' : 'text-gray-500 hover:text-black' }} px-4 cursor-pointer"
                    aria-current="{{ $activeFilter === 'popular' ? 'true' : 'false' }}">MOST POPULAR</a>
                <a href="{{ $filterUrl('interaction') }}" data-filter="interaction"
                    class="filter-link text-base {{ $activeFilter === 'interaction' ? 'text-black' : 'text-gray-500 hover:text-black' }} px-4 cursor-pointer"
                    aria-current="{{ $activeFilter === 'interaction' ? 'true' : 'false' }}">TOP INTERACTION</a>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-12 gap-6 h-full mb-10">
                <!-- Main Column 9/12 -->
                <div class="col-span-9 p-4" id="show-all-posts">
                    @forelse ($allCategoryPosts as $post)
                        <div
                            class="bg-white rounded-lg shadow p-4 pr-6 flex flex-row gap-6 items-center mb-4 hover:shadow-lg transition min-h-[120px]">
                            <img src="{{ $post->additionFile ?? '/images/free-images-for-blog.png' }}" alt="Post Image"
                                class="w-32 h-32 object-cover rounded-md bg-gray-100 flex-shrink-0"
                                onerror="this.src='/images/free-images-for-blog.png'">
                            <div class="flex flex-col flex-1 min-w-0">
                                <a href="{{ url('/post-content-viewer/' . $post->id) }}"
                                    class="font-bold text-base text-black cursor-pointer hover:text-blue-600 hover:underline line-clamp-1"
                                    style="text-decoration: none; font-size:18px">
                                    {{ $post->title ?? 'Untitled Post' }}
                                </a>
                                <div class="text-gray-600 text-sm my-2">
                                    {{ $post->preview ?? 'No preview available' }}
                                </div>
                                <div class="flex items-center justify-between mt-1 w-full">
                                    @if ($post->author)
                                        <a href="{{ url('/user-profile/' . $post->author->id) }}"
                                            class="text-sm text-gray-700 font-medium hover:text-blue-600 cursor-pointer">
                                            {{ $post->author->name }}
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-500 font-medium">Unknown Author</span>
                                    @endif
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-block truncate px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold cursor-pointer">
                                            {{ optional($post->category)->content ?? 'No category' }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ optional($post->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500">No posts yet.</div>
                    @endforelse

                    @if ($allCategoryPosts->hasPages())
                        <div class="flex justify-center mt-6">
                            <nav aria-label="Page navigation">
                                <ul class="inline-flex items-center -space-x-px">
                                    @if ($allCategoryPosts->onFirstPage())
                                        <li>
                                            <span
                                                class="px-3 py-2 leading-tight text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed">Previous</span>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ $allCategoryPosts->previousPageUrl() }}"
                                                class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">Previous</a>
                                        </li>
                                    @endif

                                    @foreach ($allCategoryPosts->getUrlRange(1, $allCategoryPosts->lastPage()) as $page => $url)
                                        <li>
                                            <a href="{{ $url }}"
                                                class="px-3 py-2 leading-tight {{ $page == $allCategoryPosts->currentPage() ? 'text-blue-600 bg-blue-50 border-blue-300' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700' }} border">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endforeach

                                    @if ($allCategoryPosts->hasMorePages())
                                        <li>
                                            <a href="{{ $allCategoryPosts->nextPageUrl() }}"
                                                class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">Next</a>
                                        </li>
                                    @else
                                        <li>
                                            <span
                                                class="px-3 py-2 leading-tight text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed">Next</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>

                <!-- Sidebar Column 3/12 -->
                <div class="col-span-3 rounded-lg">
                    <div class="w-full p-4 bg-white shadow-md rounded-xl mt-4">
                        <h4 class="mb-4 text-base text-black">OUR BEST AUTHORS</h4>
                        @forelse ($bestAuthors as $author)
                            <div class="w-full flex items-center py-2 border-b border-gray-200">
                                <div class="flex-shrink-0">
                                    <img src="{{ $author->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg' }}"
                                        alt="{{ $author->name }}" class="w-10 h-10 rounded-full">
                                </div>
                                <div class="ml-3">
                                    <a href="{{ url('/user-profile/' . $author->id) }}"
                                        class="font-semibold text-gray-800 hover:text-blue-600">
                                        {{ $author->name }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="w-full mt-8">
                        <div class="w-full p-4 bg-white shadow-md rounded-xl">
                            <h4 class="mb-4 text-base text-black">ALL TOPICS</h4>
                            @forelse ($allCategory as $category)
                                <a href="{{ url('/category/' . $category->id) }}"
                                    class="inline-block truncate px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold cursor-pointer">
                                    {{ $category->content }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="w-full mt-8">
                        <div class="bg-white shadow-md rounded-xl p-4 w-full max-w-xs">
                            <h4 class="mb-4 text-base text-black">GUIDEBOOK</h4>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-[20px] h-[20px]">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <a class="cursor-pointer hover:text-blue-700">For Beginner Writers</a>
                                </li>
                                <li class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-[20px] h-[20px]">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                    </svg>
                                    <a class="cursor-pointer hover:text-blue-700">Advanced Writing Skills</a>
                                </li>
                            </ul>
                        </div>
                        <div class="w-full mt-8">
                            <div class="bg-white shadow-md rounded-xl p-4 w-full max-w-xs">
                                <h4 class="mb-4 text-base text-black">CONTACT US</h4>
                                <div class="flex gap-4">
                                    <a href="#" class="text-gray-400 hover:text-blue-600" title="Twitter">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M22.46 6c-.77.35-1.6.59-2.47.7a4.3 4.3 0 001.88-2.37 8.59 8.59 0 01-2.72 1.04A4.28 4.28 0 0016.11 4c-2.37 0-4.29 1.92-4.29 4.29 0 .34.04.67.1.99C7.69 9.13 4.07 7.38 1.64 4.7c-.37.64-.58 1.38-.58 2.17 0 1.5.76 2.82 1.92 3.6-.7-.02-1.36-.21-1.94-.53v.05c0 2.1 1.5 3.85 3.5 4.25-.36.1-.74.16-1.13.16-.28 0-.54-.03-.8-.08.54 1.7 2.1 2.94 3.95 2.97A8.6 8.6 0 012 19.54c-.29 0-.57-.02-.85-.05A12.13 12.13 0 007.29 21.5c7.55 0 11.68-6.26 11.68-11.68 0-.18-.01-.36-.02-.54A8.18 8.18 0 0024 4.59a8.36 8.36 0 01-2.54.7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a href="https://www.facebook.com/nguyen.khanh.201930"
                                        class="text-gray-400 hover:text-blue-600" title="Facebook">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.408.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.408 24 22.674V1.326C24 .592 23.406 0 22.675 0">
                                            </path>
                                        </svg>
                                    </a>
                                    <a href="https://github.com/KhanhNguyenVimaru"
                                        class="text-gray-400 hover:text-blue-600" title="GitHub">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.084-.729.084-.729 1.205.084 1.84 1.236 1.84 1.236 1.07 1.834 2.809 1.304 3.495.997.108-.775.418-1.305.762-1.605-2.665-.305-5.466-1.334-5.466-5.931 0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 0 0 1.008-.322 3.301 1.23a11.52 11.52 0 013.003-.404c1.018.005 2.045.138 3.003.404 2.291-1.553 3.297-1.23 3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.803 5.624-5.475 5.921.43.372.823 1.102.823 2.222 0 1.606-.014 2.898-.014 3.293 0 .322.218.694.825.576C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

    @include('footer')
</body>

</html>
