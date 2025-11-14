const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

const getViewedUserId = () => {
    const body = document.querySelector('body[data-profile-user-id]');
    return body ? body.dataset.profileUserId : '';
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

const hideAlerts = () => {
    const alerts = document.querySelectorAll('#alert');
    alerts.forEach((alert) => {
        alert.style.display = 'none';
    });
};

const attachFollowHandlers = (userId) => {
    const followBtn = document.getElementById('follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', () => {
            fetch(`/follow-user/${userId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Follow failed!');
                    }
                })
                .catch(() => alert('Follow failed!'));
        });
    }

    const requestBtn = document.getElementById('request-btn');
    if (requestBtn) {
        requestBtn.addEventListener('click', () => {
            fetch(`/follow-user/${userId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Request failed!');
                    }
                })
                .catch(() => alert('Request failed!'));
        });
    }

    const unfollowBtn = document.getElementById('unfollow-btn');
    if (unfollowBtn) {
        unfollowBtn.addEventListener('click', () => {
            fetch(`/delete-follow/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Unfollow failed!');
                    }
                })
                .catch(() => alert('Unfollow failed!'));
        });
    }

    const revokeRequestBtn = document.getElementById('revoke-request-btn');
    if (revokeRequestBtn) {
        revokeRequestBtn.addEventListener('click', () => {
            fetch(`/revoke-follow-request/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Revoke request failed!');
                    }
                })
                .catch(() => alert('Revoke request failed!'));
        });
    }

    const banBtn = document.getElementById('ban-btn');
    if (banBtn) {
        banBtn.addEventListener('click', () => {
            fetch(`/delete-ban/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Ban failed!');
                    }
                })
                .catch(() => alert('Ban failed!'));
        });
    }
};

const attachMenuHandlers = () => {
    const button = document.getElementById('menuButton');
    const dropdown = document.getElementById('menuDropdown');
    if (!button || !dropdown) {
        return;
    }
    button.addEventListener('click', () => {
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
};

const loadAuthorPosts = (userId) => {
    fetch(`/content-of-author/${userId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then((response) => response.json())
        .then((payload) => {
            if (payload && payload.success === false) {
                throw new Error(payload.message || 'Failed to load author posts');
            }
            const posts = Array.isArray(payload?.data) ? payload.data : [];
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
                const createdAt = new Date(post.created_at).toLocaleString();
                const coverImg = post.additionFile || '/images/free-images-for-blog.png';
                const postDiv = document.createElement('div');
                postDiv.className = 'bg-white rounded-lg shadow p-4 flex flex-row gap-4 items-center mb-4 hover:shadow-lg transition min-h-[120px]';
                postDiv.innerHTML = `
                    <img src="${coverImg}" alt="Post Image" class="w-20 h-20 object-cover rounded-md bg-gray-100" onerror="this.src='/images/free-images-for-blog.png'">
                    <div class="flex-1 min-w-0">
                        <a href="/post-content-viewer/${post.id}" class="font-bold text-base text-black cursor-pointer post-title-hover hover:text-blue-600 hover:underline-0 line-clamp-2 h-[48px]" style="text-decoration: none;">${post.title}</a>
                        <div class="text-gray-600 text-sm mt-1 line-clamp-2">${post.preview || ''}</div>
                        <div class="flex flex-row items-center gap-2 mt-2">
                            <span class="inline-block truncate px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold cursor-pointer">${categoryName}</span>
                            <span class="text-xs text-gray-400 ml-auto">${createdAt}</span>
                        </div>
                    </div>
                `;
                postsList.appendChild(postDiv);
            });
        })
        .catch((error) => {
            console.error('Failed to load author posts:', error);
            const postsList = document.getElementById('posts-row-all');
            if (postsList) {
                postsList.innerHTML = '<div class="text-red-500">Failed to load posts.</div>';
            }
        });
};

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(hideAlerts, 3000);

    const followersLink = document.getElementById('followers-link');
    if (followersLink) {
        followersLink.addEventListener('click', (event) => {
            event.preventDefault();
            window.openModal('modal-followers');
        });
    }

    const followingLink = document.getElementById('following-link');
    if (followingLink) {
        followingLink.addEventListener('click', (event) => {
            event.preventDefault();
            window.openModal('modal-following');
        });
    }

    const privacyBadge = document.querySelector('.bg-indigo-100.text-indigo-700, .bg-gray-200.text-gray-700');
    if (privacyBadge) {
        privacyBadge.style.cursor = 'pointer';
        privacyBadge.addEventListener('click', () => {
            window.openModal('modal-privacy');
        });
    }

    attachMenuHandlers();

    const userId = getViewedUserId();
    if (!userId) {
        return;
    }

    attachFollowHandlers(userId);
    loadAuthorPosts(userId);
});
