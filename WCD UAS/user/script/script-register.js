// Script Register

document.addEventListener('DOMContentLoaded', function() {
    // --- REGISTER ---
    const registerForm = document.querySelector('form[method="POST"]');
    if (registerForm && window.location.pathname.includes('register.php')) {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm-password');
        const termsCheck = document.getElementById('terms');
        let errorAlert = document.querySelector('.alert-danger');

        // Show/hide password
        [passwordInput, confirmInput].forEach(function(input) {
            if (!input) return;
            const wrapper = input.parentElement;
            let toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-outline-secondary btn-sm';
            toggleBtn.tabIndex = -1;
            toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
            toggleBtn.style.marginLeft = '5px';
            wrapper.appendChild(toggleBtn);
            toggleBtn.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    toggleBtn.innerHTML = '<i class="fa fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
                }
            });
        });

        registerForm.addEventListener('submit', function(e) {
            let error = '';
            const name = nameInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!name || !email || !password || !confirm) {
                error = 'Semua field wajib diisi.';
            } else if (!emailPattern.test(email)) {
                error = 'Format email tidak valid.';
            } else if (password.length < 6) {
                error = 'Password minimal 6 karakter.';
            } else if (password !== confirm) {
                error = 'Konfirmasi password tidak cocok.';
            } else if (!termsCheck.checked) {
                error = 'Anda harus menyetujui syarat & ketentuan.';
            }

            if (error) {
                e.preventDefault();
                if (!errorAlert) {
                    errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger alert-dismissible fade show py-2';
                    errorAlert.innerHTML = '<div class="d-flex align-items-center"><i class="fa fa-exclamation-triangle me-2"></i><span></span></div><button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>';
                    registerForm.parentElement.insertBefore(errorAlert, registerForm);
                }
                errorAlert.querySelector('span').textContent = error;
                errorAlert.style.display = 'block';
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        });
    }
});


