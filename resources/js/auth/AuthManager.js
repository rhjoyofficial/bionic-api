// Password Toggle Logic
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form Submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const btn = document.getElementById('submitBtn');
            const errorBox = document.getElementById('error-message');

            errorBox.classList.add('hidden');
            errorBox.innerText = '';
            const originalText = btn.innerText;
            btn.innerText = 'অপেক্ষা করুন...'; // Bengali for "Please wait..."
            btn.disabled = true;

            const guestSessionToken = localStorage.getItem('cartToken') || '';
            document.getElementById('session_token').value = guestSessionToken;

            const formData = new FormData(form);

            try {
                // Updated URL to match your api.php prefix
                const response = await fetch('{{ url('/api/v1/login') }}', {
                    method: 'POST',
                    headers: this._getHeaders(),
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.data.token) {
                        localStorage.setItem('auth_token', data.data.token);
                    }
                    localStorage.removeItem('cart_session_token');
                    window.location.href = '/';
                } else {
                    errorBox.innerText = data.message || 'লগইন ব্যর্থ হয়েছে। আবার চেষ্টা করুন।';
                    errorBox.classList.remove('hidden');
                }
            } catch (error) {
                errorBox.innerText = 'সার্ভারের সাথে যোগাযোগ করা যাচ্ছে না।';
                errorBox.classList.remove('hidden');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });

          document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const btn = document.getElementById('submitBtn');
            const errorBox = document.getElementById('error-box');
            const errorList = document.getElementById('error-list');

            // Reset UI
            errorBox.classList.add('hidden');
            errorList.innerHTML = '';
            const originalText = btn.innerText;
            btn.innerText = 'অপেক্ষা করুন...'; // "Please wait..."
            btn.disabled = true;

            // Grab guest session for cart merge
            const guestSessionToken = localStorage.getItem('cartToken') || '';
            document.getElementById('session_token').value = guestSessionToken;

            const formData = new FormData(form);
            console.log('Form Data:', Object.fromEntries(formData.entries())); // Debugging line
            return; // Remove this line after debugging
            try {
                const response = await fetch('{{ url('/api/v1/register') }}', {
                    method: 'POST',
                    headers: this._getHeaders(),
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.data.token) {
                        localStorage.setItem('auth_token', data.data.token);
                    }
                    localStorage.removeItem('cartToken');
                    window.location.href = '/';
                } else {
                    // Parse validation errors
                    if (data.errors) {
                        Object.values(data.errors).flat().forEach(err => {
                            const li = document.createElement('li');
                            li.innerText = err;
                            errorList.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.innerText = data.message || 'নিবন্ধন ব্যর্থ হয়েছে।';
                        errorList.appendChild(li);
                    }
                    errorBox.classList.remove('hidden');
                }
            } catch (error) {
                const li = document.createElement('li');
                li.innerText = 'সার্ভারের সাথে যোগাযোগ করা যাচ্ছে না।';
                errorList.appendChild(li);
                errorBox.classList.remove('hidden');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
        