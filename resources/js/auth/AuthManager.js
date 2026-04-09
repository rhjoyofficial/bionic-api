/**
 * AuthManager — handles login, register, forgot-password, reset-password, and logout.
 *
 * Login and register POST to /login and /register (web middleware group) so that
 * PHP sessions are established and @auth directives work in all subsequent Blade
 * renders.  The Sanctum token returned by those endpoints is stored in
 * localStorage for authorised JS API calls (cart, checkout, etc.).
 *
 * Reads the guest cart token from the bionic_cart_token cookie (httpOnly=false)
 * and passes it as `session_token` so the backend can merge the guest cart
 * into the newly authenticated user's cart.
 */
export default class AuthManager {
    constructor() {
        this.sessionToken = this._readCartToken();

        const form = {
            login: document.getElementById("loginForm"),
            register: document.getElementById("registerForm"),
            forgot: document.getElementById("forgotForm"),
            reset: document.getElementById("resetForm"),
        };

        this.logoutBtn = document.getElementById("logoutBtn");

        if (form.login) this._initLogin(form.login);
        if (form.register) this._initRegister(form.register);
        if (form.forgot) this._initForgot(form.forgot);
        if (form.reset) this._initReset(form.reset);
        if (this.logoutBtn) this._initLogout();

        this._bindPasswordToggles();
        this._handleResetSuccessFlash();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Read bionic_cart_token from cookie (JS-readable because httpOnly=false). */
    _readCartToken() {
        const match = document.cookie.match(
            /(?:^|;\s*)bionic_cart_token=([^;]+)/,
        );
        // console.log("Cart token read from cookie:", match ? match[1] : null);
        if (match) return decodeURIComponent(match[1]);
        // Fallback: localStorage mirrors the cookie during the session
        // console.log(localStorage.getItem("bionic_cart_token") || "ERROR");
        return localStorage.getItem("bionic_cart_token") || "";
    }

    _headers() {
        return {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN":
                document.querySelector('meta[name="csrf-token"]')?.content ??
                "",
        };
    }

    _setLoading(btn, loading) {
        btn.disabled = loading;
        if (loading) {
            btn._origText = btn.textContent;
            btn.textContent = "অপেক্ষা করুন...";
        } else {
            btn.textContent = btn._origText ?? btn.textContent;
        }
        btn.classList.toggle("opacity-70", loading);
    }

    _showError(box, msg) {
        if (!box) return;
        box.textContent = msg;
        box.classList.remove("hidden");
    }

    _showErrorList(box, list, errors) {
        if (!box || !list) return;
        list.innerHTML = "";
        errors.forEach((err) => {
            const li = document.createElement("li");
            li.textContent = err;
            list.appendChild(li);
        });
        box.classList.remove("hidden");
        list.classList.remove("hidden");
        // console.log("Full container after adding errors:", box);
    }

    _clearErrors(...boxes) {
        boxes.forEach((b) => {
            if (!b) return;
            b.classList.add("hidden");
            // also clear child lists
            const ul = b.querySelector("ul");
            if (ul) {
                ul.innerHTML = "";
            } else {
                b.textContent = "";
            }
        });
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    _initLogout() {
        this.logoutBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            this._setLoading(this.logoutBtn, true);

            try {
                // POST to the web logout route which invalidates the PHP session
                // AND revokes all Sanctum tokens in one request.
                await fetch("/logout", {
                    method: "POST",
                    headers: this._headers(),
                });
            } catch (err) {
                window.flash?.("লগআউট ব্যর্থ হয়েছে!", "error", 2000);
                this._setLoading(this.logoutBtn, false);
            } finally {
                window.flash?.("লগআউট সফল হয়েছে!", "success", 2000);
                // Always clear local storage and redirect regardless of server response.
                localStorage.removeItem("auth_token");
                localStorage.removeItem("bionic_cart_token");
                window.location.href = "/login";
            }
        });
    }

    // ── Login ────────────────────────────────────────────────────────────────

    _initLogin(form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            const errorBox = document.getElementById("error-message");

            this._clearErrors(errorBox);
            this._setLoading(btn, true);

            const rawLogin = form.querySelector('[name="login"]').value.trim();

            const cleanLogin =
                window.ValidationManager.cleanLoginInput(rawLogin);

            if (!cleanLogin) {
                this._setLoading(btn, false);
                return this._showError(
                    errorBox,
                    "সঠিক ইমেইল বা মোবাইল নম্বর লিখুন (১১ ডিজিট)।",
                );
            }

            try {
                const res = await fetch("/login", {
                    method: "POST",
                    headers: this._headers(),
                    body: JSON.stringify({
                        login: cleanLogin,
                        password: form.querySelector('[name="password"]').value,
                        session_token: this.sessionToken,
                        remember:
                            form.querySelector('[name="remember"]')?.checked ??
                            false,
                    }),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    window.flash?.("লগইন সফল হয়েছে!", "success", 2000);
                    localStorage.setItem("auth_token", data.data.token);
                    localStorage.removeItem("bionic_cart_token");
                    this._refreshCsrfMeta(res);

                    const intended = new URLSearchParams(
                        window.location.search,
                    ).get("redirect");
                    setTimeout(() => {
                        window.location.href = intended || "/";
                    }, 1500);
                } else {
                    this._showError(
                        errorBox,
                        data.message ||
                            "লগইন ব্যর্থ হয়েছে। তথ্যগুলো আবার যাচাই করুন।",
                    );
                }
            } catch {
                this._showError(
                    errorBox,
                    "সার্ভারের সাথে যোগাযোগ করা যাচ্ছে না।",
                );
            } finally {
                this._setLoading(btn, false);
            }
        });
    }

    // ── Register ─────────────────────────────────────────────────────────────

    _initRegister(form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            const errorBox = document.getElementById("error-box");
            const errorList = document.getElementById("error-list");

            this._clearErrors(errorBox, errorList);
            this._setLoading(btn, true);

            const rawPhone = form.querySelector('[name="phone"]').value.trim();
            const rawEmail = form
                .querySelector('[name="email"]')
                ?.value?.trim();

            // TRANSFORM THE DATA
            const cleanPhone = window.ValidationManager.cleanPhone(rawPhone);
            const cleanEmail = rawEmail
                ? window.ValidationManager.cleanEmail(rawEmail)
                : null;

            // --- VALIDATION CHECKS ---
            if (!cleanPhone) {
                this._setLoading(btn, false);
                return this._showErrorList(errorBox, errorList, [
                    "একটি সঠিক মোবাইল নম্বর লিখুন।",
                ]);
            }

            if (rawEmail && !cleanEmail) {
                this._setLoading(btn, false);
                return this._showErrorList(errorBox, errorList, [
                    "দয়া করে শুধুমাত্র Gmail বা Yahoo ইমেইল ব্যবহার করুন।",
                ]);
            }

            try {
                const res = await fetch("/register", {
                    method: "POST",
                    headers: this._headers(),
                    body: JSON.stringify({
                        name: form.querySelector('[name="name"]').value.trim(),
                        email: cleanEmail,
                        phone: cleanPhone,
                        password: form.querySelector('[name="password"]').value,
                        password_confirmation: form.querySelector(
                            '[name="password_confirmation"]',
                        ).value,
                        session_token: this.sessionToken,
                    }),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    window.flash?.("নিবন্ধন সফল হয়েছে!", "success", 2000);
                    localStorage.setItem("auth_token", data.data.token);
                    localStorage.removeItem("bionic_cart_token");
                    // Session was regenerated server-side — update CSRF meta tag.
                    this._refreshCsrfMeta(res);
                    // Redirect to home so the page reloads with @auth active.
                    setTimeout(() => {
                        window.location.href = "/";
                    }, 1500);
                } else {
                    const errors = data.errors
                        ? Object.values(data.errors).flat()
                        : [data.message || "নিবন্ধন ব্যর্থ হয়েছে।"];
                    // console.log("Registration errors:", errors);
                    this._showErrorList(errorBox, errorList, errors);
                }
            } catch (err) {
                this._showErrorList(errorBox, errorList, [
                    "সার্ভারের সাথে যোগাযোগ করা যাচ্ছে না।",
                ]);
            } finally {
                this._setLoading(btn, false);
            }
        });
    }

    // ── Forgot Password ───────────────────────────────────────────────────────

    _initForgot(form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            const errorBox = document.getElementById("forgot-error");
            const successBox = document.getElementById("forgot-success");

            this._clearErrors(errorBox);
            successBox?.classList.add("hidden");
            this._setLoading(btn, true);

            try {
                const res = await fetch("/api/v1/forgot-password", {
                    method: "POST",
                    headers: this._headers(),
                    body: JSON.stringify({
                        email: form
                            .querySelector('[name="email"]')
                            .value.trim(),
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    if (successBox) {
                        successBox.textContent =
                            data.message ||
                            "Password reset link sent. Please check your email.";
                        successBox.classList.remove("hidden");
                    }
                    form.reset();
                } else {
                    this._showError(
                        errorBox,
                        data.message || "Failed to send reset link.",
                    );
                }
            } catch {
                this._showError(
                    errorBox,
                    "সার্ভারের সাথে যোগাযোগ করা যাচ্ছে না।",
                );
            } finally {
                this._setLoading(btn, false);
            }
        });
    }

    // ── Reset Password ────────────────────────────────────────────────────────

    _initReset(form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            const errorBox = document.getElementById("reset-error");

            this._clearErrors(errorBox);
            this._setLoading(btn, true);

            try {
                const res = await fetch("/api/v1/password/reset", {
                    method: "POST",
                    headers: this._headers(),
                    body: JSON.stringify({
                        token: form.querySelector('[name="token"]').value,
                        email: form
                            .querySelector('[name="email"]')
                            .value.trim(),
                        password: form.querySelector('[name="password"]').value,
                        password_confirmation: form.querySelector(
                            '[name="password_confirmation"]',
                        ).value,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    window.location.href = "/login?reset=1";
                } else {
                    this._showError(
                        errorBox,
                        data.message ||
                            "Password reset failed. The link may have expired.",
                    );
                }
            } catch {
                this._showError(
                    errorBox,
                    "সার্ভারের সাথে যোগাযোগ করা যাচ্ছে না।",
                );
            } finally {
                this._setLoading(btn, false);
            }
        });
    }

    // ── Password Toggle ───────────────────────────────────────────────────────

    _bindPasswordToggles() {
        document.querySelectorAll("[data-password-toggle]").forEach((btn) => {
            btn.addEventListener("click", () => {
                const input = document.getElementById(
                    btn.dataset.passwordToggle,
                );
                const icon = btn.querySelector("i");
                if (!input) return;

                const isHidden = input.type === "password";
                input.type = isHidden ? "text" : "password";
                icon?.classList.toggle("fa-eye", !isHidden);
                icon?.classList.toggle("fa-eye-slash", isHidden);
            });
        });
    }

    // ── CSRF helpers ────────────────────────────────────────────────────────

    /**
     * After login/register the server calls session()->regenerate() which
     * rotates the CSRF token.  If the server returns the new token in an
     * X-CSRF-TOKEN response header, update the <meta> tag so that any
     * request made before the full-page redirect doesn't get a 419.
     */
    _refreshCsrfMeta(res) {
        const newToken = res.headers.get("X-CSRF-TOKEN");
        if (newToken) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute("content", newToken);
        }
    }

    // ── Misc ─────────────────────────────────────────────────────────────────

    /** Show a flash message if redirected back after successful password reset. */
    _handleResetSuccessFlash() {
        if (new URLSearchParams(window.location.search).get("reset") === "1") {
            window.flash?.(
                "Password reset successfully. Please sign in.",
                "success",
                8000,
            );
            // Clean the URL without reloading
            history.replaceState(null, "", window.location.pathname);
        }
    }
}
