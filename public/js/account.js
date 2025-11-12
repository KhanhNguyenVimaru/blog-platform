const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        return meta.getAttribute('content');
    }
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
};

const handleLogoutRequest = () => {
    const token = localStorage.getItem('token');
    return fetch('/logout', {
        method: 'POST',
        headers: {
            'Authorization': token ? `Bearer ${token}` : '',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const descriptionTextarea = document.querySelector('textarea[name="description"]');
    const charCount = document.getElementById('char-count');

    if (descriptionTextarea && charCount) {
        charCount.textContent = descriptionTextarea.value.length;
        descriptionTextarea.addEventListener('input', function onInput() {
            charCount.textContent = this.value.length;
        });
    }

    const logoutForm = document.getElementById('logout-form-account');
    const deleteAccountForm = document.getElementById('delete-account-form');
    const changePasswordForm = document.getElementById('change-password-form');
    const logoutLink = document.getElementById('logout-link');

    if (logoutForm) {
        logoutForm.addEventListener('submit', (event) => {
            event.preventDefault();
            handleLogoutRequest()
                .then((response) => {
                    if (response.ok) {
                        localStorage.clear();
                        window.location.href = '/page-login';
                        return;
                    }
                    return response.json().then((data) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error logging out',
                            text: data.message || 'An unexpected error occurred.',
                            showConfirmButton: true
                        });
                    });
                })
                .catch((error) => {
                    console.error('Error logging out:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error logging out',
                        text: 'An unexpected error occurred.',
                        showConfirmButton: true
                    });
                });
        });
    }

    if (deleteAccountForm) {
        deleteAccountForm.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Your account is scheduled to be deleted in 30 days. If you log in during this period, the deletion process will be canceled.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete my account!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                const token = localStorage.getItem('token');
                fetch('/delete-account', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': token ? `Bearer ${token}` : '',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                })
                    .then((response) => {
                        if (response.ok) {
                            localStorage.clear();
                            Swal.fire('Deleted!', 'Your account has been permanently deleted.', 'success').then(() => {
                                window.location.href = '/page-login';
                            });
                            return;
                        }
                        return response.json().then((data) => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error deleting account',
                                text: data.message || 'An unexpected error occurred.',
                                showConfirmButton: true
                            });
                        });
                    })
                    .catch((error) => {
                        console.error('Error deleting account:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error deleting account',
                            text: 'An unexpected error occurred.',
                            showConfirmButton: true
                        });
                    });
            });
        });
    }

    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const token = localStorage.getItem('token');
            const formData = new FormData(changePasswordForm);
            fetch('/change_password', {
                method: 'POST',
                headers: {
                    'Authorization': token ? `Bearer ${token}` : '',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then((response) => {
                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password changed!',
                            text: 'Your password has been changed successfully.',
                            showConfirmButton: true
                        }).then(() => {
                            window.location.href = '/page-account';
                        });
                        return;
                    }
                    return response.json().then((data) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error changing password',
                            text: data.message || 'An unexpected error occurred.',
                            showConfirmButton: true
                        });
                    });
                })
                .catch((error) => {
                    console.error('Error changing password:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error changing password',
                        text: 'An unexpected error occurred.',
                        showConfirmButton: true
                    });
                });
        });
    }

    if (logoutLink) {
        logoutLink.addEventListener('click', (event) => {
            event.preventDefault();
            handleLogoutRequest().then((response) => {
                if (response.ok) {
                    localStorage.clear();
                    window.location.href = '/page-login';
                }
            });
        });
    }
});
