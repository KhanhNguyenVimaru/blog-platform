<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Account - Blog</title>
    <link rel="icon" type="image/x-icon" href="https://www.svgrepo.com/show/475713/blog.svg" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    @include('header')

    <div class="flex justify-center items-start min-h-[calc(100vh-64px)] p-0 mt-0 pt-0">
        <div class="bg-white w-1/2 rounded-2xl shadow-2xl border border-gray-200 p-8 mt-4 mb-10">
            <div class="space-y-6">
                <div class="h-full h-[100px] flex items-center justify-center">
                    <div class="flex flex-col items-center justify-center">
                        <div
                            class="flex items-center justify-center w-28 h-28 rounded-full bg-gray-200 overflow-hidden border border-gray-200">
                            <img id="avatarPreview"
                                src="{{ Auth::user()->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg' }}"
                                alt="User Avatar" class="w-full h-full rounded-full object-cover cursor-pointer ">
                        </div>
                        <input type="file" id="avatarInput" accept="image/*" style="display:none">
                        <!-- Modal cropper -->
                        <div id="cropModal"
                            style="display:none; position:fixed; z-index:9999; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
                            <div
                                style="background:#fff; padding:24px; border-radius:12px; max-width:90vw; max-height:90vh; display:flex; flex-direction:column; align-items:center;">
                                <img id="cropImage" src=""
                                    style="max-width:500px; max-height:500px; display:block;">
                                <button id="cropBtn"
                                    class="mt-4 px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Crop &
                                    Preview</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Title -->
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-600 text-center">
                    Account Settings
                </h1>

                <!-- Full name -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-600">Full name</label>
                    <div class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 text-base">
                        {{ Auth::user()->name }}
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-600">Email address</label>
                    <div class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 text-base">
                        {{ Auth::user()->email }}
                    </div>
                </div>

                <!-- Privacy + Description -->
                @php
                    $user = Auth::user();
                @endphp
                <form class="space-y-4" method="POST" action="{{ route('updateUserData', $user->id) }}">
                    @csrf
                    @method('PATCH')

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-base">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-base">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Privacy -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">Privacy</label>
                        <select
                            class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 w-full text-base"
                            style="height: 40px; important;" name = "privacy" value = "{{ $user->privacy }}">
                            <option value="public" {{ old('privacy', $user->privacy) == 'public' ? 'selected' : '' }}>
                                Public</option>
                            <option value="private" {{ old('privacy', $user->privacy) == 'private' ? 'selected' : '' }}>
                                Private</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-0">
                        <label class="block mb-2 text-sm font-medium text-gray-600">Description</label>
                        <textarea class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 w-full resize-none text-base"
                            rows="4" placeholder="Description (max 255 characters)" name="description" maxlength="255">{{ old('description', $user->description) }}</textarea>
                        <div class="text-sm text-gray-500 mt-1">
                            <span id="char-count">0</span>/255 characters
                        </div>
                    </div>

                    <!-- Save settings button -->
                    <div class="flex justify-end">
                        <button type="submit"
                            class="w-1/4 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5 text-center cursor-pointer h-8">
                            Save settings
                        </button>
                    </div>
                </form>
                <!-- Change password -->
                <form id="change-password-form" class="space-y-4" method="POST" action="/change_password">
                    @csrf
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">Current password</label>
                        <input type="password" name="current_password"
                            class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 w-full text-base"
                            placeholder="Current password" required />
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">New password</label>
                        <input type="password" name="new_password"
                            class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 w-full text-base"
                            placeholder="New password" required />
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">Confirm new password</label>
                        <input type="password" name="new_password_confirmation"
                            class="bg-gray-50 border border-gray-200 text-gray-600 rounded px-3 py-2 w-full text-base"
                            placeholder="Confirm new password" required />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="mt-[10px] text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5 text-center w-1/4 cursor-pointer h-8">
                            Change password
                        </button>
                    </div>
                </form>
                <hr class="my-6 border-gray-200 w-9/10 mx-auto" />

                <!-- Logout and Delete Account -->
                <div class="flex gap-4 mt-10">
                    <!-- Delete Account -->
                    <form id="delete-account-form" action="{{ route('deleteUserAccount') }}" method="POST"
                        class="flex-1">
                        @csrf
                        @method('DELETE')
                        <div class="flex justify-end">
                            <button type="submit"
                                class="mt-5 flex items-center justify-center gap-2 px-3 py-1.5 bg-transparent text-red-600 hover:text-red-400 rounded-lg font-medium text-sm focus:outline-none focus:ring-2 focus:ring-red-100 w-full transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                                Delete Account
                            </button>
                        </div>
                    </form>
                    <!-- Logout -->
                    <form id="logout-form-account" action="/logout" method="POST" class="flex-1">
                        @csrf
                        <div class="flex justify-end">
                            <button type="submit"
                                class="mt-5 flex items-center justify-center gap-2 px-3 py-1.5 bg-transparent text-red-600 hover:text-red-400 rounded-lg font-medium text-sm focus:outline-none focus:ring-2 focus:ring-red-100 w-full transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m-6-3h12m0 0l-3-3m3 3l-3 3" />
                                </svg>
                                Log out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden logout form -->
    <form id="logout-form" action="/logout" method="POST" style="display: none;">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/account.js') }}" defer></script>
</body>

</html>
