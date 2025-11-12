import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';
document.addEventListener('DOMContentLoaded', function () {
    // Avatar crop logic
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    let cropper;
    let cropModal = document.getElementById('cropModal');
    let cropBtn = document.getElementById('cropBtn');
    let cropImage = document.getElementById('cropImage');

    if (avatarInput) {
        avatarInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    cropImage.src = event.target.result;
                    cropModal.style.display = 'flex';
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(cropImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        preview: avatarPreview
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    }
    if (cropBtn) {
        cropBtn.addEventListener('click', function () {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 256,
                    height: 256
                });
                canvas.toBlob(function (blob) {
                    const formData = new FormData();
                    formData.append('avatar', blob, 'avatar.png');
                    fetch('/update-avatar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.avatar_url) {
                                avatarPreview.src = data.avatar_url + '?t=' + Date
                                    .now();
                            } else {
                                alert('Upload failed!');
                            }
                        })
                        .catch(() => alert('Upload failed!'));
                }, 'image/png');
                cropModal.style.display = 'none';
                if (cropper) cropper.destroy();
            }
        });
    }
    // Close modal on click outside
    if (cropModal) {
        cropModal.addEventListener('click', function (e) {
            if (e.target === cropModal) {
                cropModal.style.display = 'none';
                if (cropper) cropper.destroy();
            }
        });
    }
    if (avatarInput && avatarPreview) {
        avatarPreview.addEventListener('click', function () {
            avatarInput.click();
        });
    }
});
// Helper to get CSRF token from meta or input
function getCsrfToken() {
    let token = document.querySelector('meta[name="csrf-token"]');
    if (token) return token.getAttribute('content');
    let input = document.querySelector('input[name="_token"]');
    if (input) return input.value;
    return '';
}

// AJAX Signup
const signupForm = document.getElementById('signupForm');
const signupSpinner = document.getElementById('signupSpinner');
if (signupForm) {
    signupForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(signupForm);
        if (signupSpinner) signupSpinner.style.display = 'flex';
        try {
            const response = await fetch('/handle-signup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: formData
            });
            const data = await response.json();
            if (signupSpinner) signupSpinner.style.display = 'none';
            if (response.ok) {
                Swal.fire({ icon: 'success', title: 'Registration successful!', text: 'A verification email has been sent. Please check your inbox to verify your account.' }).then(() => {
                    window.location.href = '/signup-success';
                });
            } else {
                let msg = data.message || data.error || 'Registration failed';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch (err) {
            if (signupSpinner) signupSpinner.style.display = 'none';
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred, please try again.' });
        }
    });
}

// AJAX Login
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(loginForm);
        try {
            const response = await fetch('/handle-login', { // có fetch là nối thẳng tới route
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: formData
            });
            const data = await response.json();
            if (response.ok) {
                localStorage.setItem('token', data.access_token);
                localStorage.setItem('user', data.user);
                Swal.fire({ icon: 'success', title: 'Login successful!', text: 'Welcome back ' + data.user }).then(() => {
                    window.location.href = '/';
                });
            } else {
                let msg = data.message || data.error || 'Login failed';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred, please try again.' });
        }
    });
}
