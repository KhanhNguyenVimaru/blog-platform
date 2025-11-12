const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

window.openModal = (id) => {
    const modal = document.getElementById(id);
    const panel = document.getElementById(`${id}-panel`);
    if (!modal) {
        return;
    }
    modal.classList.remove('pointer-events-none', 'opacity-0');
    modal.classList.add('opacity-100');
    setTimeout(() => {
        if (panel) {
            panel.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            panel.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }
    }, 10);
    const handler = (event) => {
        if (panel && !panel.contains(event.target)) {
            window.closeModal(id);
            modal.removeEventListener('mousedown', handler);
        }
    };
    modal.addEventListener('mousedown', handler);
};

window.closeModal = (id) => {
    const modal = document.getElementById(id);
    const panel = document.getElementById(`${id}-panel`);
    if (!modal || !panel) {
        return;
    }
    panel.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
    panel.classList.add('opacity-0', 'scale-95', 'translate-y-4');
    setTimeout(() => {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }, 200);
};

document.addEventListener('DOMContentLoaded', () => {
    const followersLink = document.getElementById('followers-link');
    const followingLink = document.getElementById('following-link');
    const privacyBadge = document.querySelector('.bg-indigo-100.text-indigo-700, .bg-gray-200.text-gray-700');

    if (followersLink) {
        followersLink.addEventListener('click', (event) => {
            event.preventDefault();
            window.openModal('modal-followers');
        });
    }

    if (followingLink) {
        followingLink.addEventListener('click', (event) => {
            event.preventDefault();
            window.openModal('modal-following');
        });
    }

    if (privacyBadge) {
        privacyBadge.style.cursor = 'pointer';
        privacyBadge.addEventListener('click', () => {
            window.openModal('modal-privacy');
        });
    }

    fetch('/content-of-users', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then((response) => response.json())
        .then((posts) => {
            const postsCountEls = document.querySelectorAll('.js-posts-count');
            postsCountEls.forEach((element) => {
                element.textContent = posts.length;
            });
            const postsList = document.getElementById('posts-row-all');
            if (!postsList) {
                return;
            }
            postsList.innerHTML = '';
            if (!posts.length) {
                postsList.innerHTML = '<div class="text-gray-500">No posts yet.</div>';
                return;
            }
            posts.forEach((post) => {
                const categoryName = post.category ? post.category.content : 'No category';
                const status = post.status.charAt(0).toUpperCase() + post.status.slice(1);
                const createdAt = new Date(post.created_at).toLocaleString();
                const coverImg = post.additionFile || '/images/free-images-for-blog.png';
                const postDiv = document.createElement('div');
                postDiv.className = 'bg-white rounded-lg shadow p-4 pr-2 flex flex-row gap-4 items-center mb-4 hover:shadow-lg transition min-h-[120px]';
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

window.toggleDropdown = (postId) => {
    const dropdown = document.getElementById(`dropdown-${postId}`);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
};

document.addEventListener('click', (event) => {
    const dropdowns = document.getElementsByClassName('dropdown-menu');
    const toggles = document.getElementsByClassName('dropdown-toggle');
    const isToggle = Array.from(toggles).some((toggle) => toggle.contains(event.target));
    if (!isToggle) {
        Array.from(dropdowns).forEach((dropdown) => dropdown.classList.add('hidden'));
    }
});

window.updateStatus = async (postId, status) => {
    try {
        const response = await fetch(`/update-status/${postId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ status })
        });
        if (response.ok) {
            window.location.reload();
        } else {
            alert('Failed to update status');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
};

window.deletePost = async (postId) => {
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

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await fetch(`/delete-post/${postId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        if (response.ok) {
            await Swal.fire({
                title: 'Deleted!',
                text: 'The post has been deleted successfully.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            window.location.reload();
        } else {
            Swal.fire('Failed', 'Could not delete the post.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'An unexpected error occurred.', 'error');
    }
};
