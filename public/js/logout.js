document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const token = localStorage.getItem('token');
            // Try to get CSRF token from meta or input if available
            let csrfToken = null;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                csrfToken = meta.getAttribute('content');
            } else {
                const input = document.querySelector('input[name="_token"]');
                if (input) csrfToken = input.value;
            }
            fetch('/logout', {
                method: 'POST',
                headers: {
                    'Authorization': token ? `Bearer ${token}` : '',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    localStorage.clear();
                    window.location.href = '/page-login';
                } else {
                    alert('Logout failed.');
                }
            })
            .catch(() => {
                alert('Logout failed.');
            });
        });
    }
});
