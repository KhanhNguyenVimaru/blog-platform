(() => {
    const header = document.querySelector('header[data-user-profile-url]');
    if (!header) {
        return;
    }

    const config = {
        userProfileUrlBase: header.dataset.userProfileUrl || '',
        postAddressBase: header.dataset.postUrl || '',
        authId: header.dataset.authId || ''
    };

    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    document.addEventListener('DOMContentLoaded', () => {
        const loginLink = document.getElementById('login-link');
        const accountWrapper = document.getElementById('account-dropdown-wrapper');
        const accountLink = document.getElementById('account-link');
        const accountDropdown = document.getElementById('account-dropdown');
        const mobileWrapper = document.getElementById('mobile-account-wrapper');

        if (loginLink && accountWrapper) {
            if (localStorage.getItem('token') === null) {
                loginLink.style.display = 'block';
                accountWrapper.style.display = 'none';
                if (mobileWrapper) {
                    mobileWrapper.style.display = 'none';
                }
            } else {
                loginLink.style.display = 'none';
                accountWrapper.style.display = 'block';
                if (mobileWrapper) {
                    mobileWrapper.style.display = 'block';
                }
            }
        }

        if (accountLink && accountDropdown) {
            document.addEventListener('click', (event) => {
                if (!accountDropdown.contains(event.target) && !accountLink.contains(event.target)) {
                    accountDropdown.classList.add('hidden');
                }
            });

            accountLink.addEventListener('click', (event) => {
                event.preventDefault();
                accountDropdown.classList.toggle('hidden');
            });
        }

        const searchNavLink = document.getElementById('search-nav-link');
        const navLinks = document.getElementById('nav-links');
        const searchInputWrapper = document.getElementById('search-input-wrapper');
        const searchInput = document.getElementById('search-input');

        if (searchNavLink && navLinks && searchInputWrapper && searchInput) {
            searchNavLink.addEventListener('click', (event) => {
                event.preventDefault();
                navLinks.classList.add('hidden');
                searchInputWrapper.classList.add('active');
                searchInput.focus();
            });

            document.addEventListener('mousedown', (event) => {
                if (!searchInputWrapper.contains(event.target) && !searchNavLink.contains(event.target)) {
                    searchInputWrapper.classList.remove('active');
                    navLinks.classList.remove('hidden');
                }
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    const query = searchInput.value.trim();
                    if (query) {
                        window.location.href = `/search?query=${encodeURIComponent(query)}`;
                    }
                }
            });
        }

        const bell = document.getElementById('notification-bell');
        const dropdown = document.getElementById('notification-dropdown');

        if (bell && dropdown) {
            bell.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const shouldFetch = dropdown.classList.contains('hidden');
                if (shouldFetch) {
                    fetch('/loadUserNotify', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then((response) => response.json())
                        .then((result) => {
                            const notifies = Array.isArray(result.data) ? result.data : [];
                            let html = '<div class="font-semibold text-gray-700 mb-2">Notifications</div>';
                            if (!notifies.length) {
                                html += '<div class="text-gray-500 text-sm">No notifications yet.</div>';
                            } else {
                                html += notifies.map((notify) => {
                                    const userSentId = notify.send_from_id;
                                    const postId = notify.addition || '';
                                    if (notify.type === 'follow_request') {
                                        return `
                                            <div class="flex items-center justify-between gap-2 py-2 hover:bg-gray-50 h-[40px]" onclick="window.location.href='${config.userProfileUrlBase}${userSentId}'">
                                                <span class="text-sm text-gray-600 flex-1">${notify.notify_content || 'You have a new follow request.'}</span>
                                                <button class="cursor-pointer hover:bg-blue-300 accept-follow-btn bg-blue-500 text-white px-2 py-1 rounded text-xs mr-1" data-id="${notify.id}" onclick="acceptRequest(${notify.id}, ${notify.send_from_id})">Accept</button>
                                                <button class="cursor-pointer hover:bg-gray-300 reject-follow-btn bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs" data-id="${notify.id}" onclick="denyRequest(${notify.id}, ${notify.send_from_id})">X</button>
                                            </div>
                                        `;
                                    }
                                    if (notify.type === 'new_post') {
                                        return `
                                            <div class="flex items-center justify-between h-[40px] text-sm py-2 px-2 hover:bg-gray-50 text-gray-600">
                                                <span onclick="window.location.href='${config.postAddressBase}${postId}'" class="flex-1 cursor-pointer">${notify.notify_content || 'You have a new notification.'}</span>
                                                <button onclick="deleteNotify(${notify.id}, event)" class="text-gray-500 hover:text-red-600 cursor-pointer" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        `;
                                    }
                                    return `
                                        <div class="flex items-center justify-between h-[40px] text-sm py-2 px-2 hover:bg-gray-50 text-gray-600">
                                            <span onclick="window.location.href='${config.userProfileUrlBase}${userSentId}'" class="flex-1 cursor-pointer">${notify.notify_content || 'You have a new notification.'}</span>
                                            <button onclick="deleteNotify(${notify.id}, event)" class="text-gray-500 hover:text-red-600 cursor-pointer" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    `;
                                }).join('');
                            }
                            dropdown.innerHTML = html;
                        });
                }
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (event) => {
                if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });

    window.denyRequest = (id, sendFromId) => {
        if (!id || !sendFromId) {
            return;
        }
        fetch('/deny-request', {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id,
                send_from_id: sendFromId
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
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
    };

    window.acceptRequest = (id, sendFromId) => {
        if (!id || !sendFromId || !config.authId) {
            return;
        }
        fetch('/accept-request', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                followerId: sendFromId,
                authorId: config.authId
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const btn = document.querySelector(`button.accept-follow-btn[data-id='${id}']`);
                    if (btn) {
                        const span = document.createElement('span');
                        span.className = 'ml-2 text-xs text-green-500 font-semibold';
                        span.innerText = 'Accepted';
                        btn.replaceWith(span);
                    }
                    fetch('/delete-notify', {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id })
                    });
                } else {
                    alert(data.message || 'Error accepting request!');
                }
            })
            .catch(() => {
                alert('Error accepting request!');
            });
    };

    window.deleteNotify = (id, event) => {
        if (event) {
            event.stopPropagation();
        }

        const button = event?.currentTarget;
        const parentDiv = button ? button.closest('div') : null;
        if (parentDiv) {
            parentDiv.remove();
        }

        fetch('/delete-notify', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ id })
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    alert(data.message || 'Xóa thất bại.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    };
})();
