// ===============================
// FIX: the whole script now runs inside DOMContentLoaded.
// Previously every querySelector/getElementById call ran at parse time —
// if this <script> tag is ever placed in <head> without `defer`, or loads
// before the elements below exist, `wrapper` (and the others) come back
// null and every click/submit handler throws "Cannot read properties of
// null". Wrapping in DOMContentLoaded makes this safe regardless of where
// the tag is placed in Login.html.
// ===============================
document.addEventListener('DOMContentLoaded', () => {

// ===============================
// LOGIN / REGISTER SWITCH
// ===============================

const wrapper = document.querySelector('.wrapper');

const registerLink = document.querySelector('.register-link');
const loginLink    = document.querySelector('.login-link');

// FIX: pointer-events:none on the inactive panel (set in loginstyle.css)
// blocks mouse clicks but not keyboard Tab focus, so keyboard/screen-reader
// users could tab straight into the hidden form's fields. `inert` removes
// a panel from both focus and the accessibility tree while it's hidden.
const registerPanels = document.querySelectorAll('.form-box.register, .info-text.register');
const loginPanels    = document.querySelectorAll('.form-box.login, .info-text.login');

function syncInert() {
    const showingRegister = !!wrapper?.classList.contains('active');
    registerPanels.forEach(el => el.toggleAttribute('inert', !showingRegister));
    loginPanels.forEach(el => el.toggleAttribute('inert', showingRegister));
}

syncInert(); // set correct state on first load

registerLink?.addEventListener('click', e => {
    e.preventDefault();
    wrapper?.classList.add('active');
    syncInert();
});

loginLink?.addEventListener('click', e => {
    e.preventDefault();
    wrapper?.classList.remove('active');
    syncInert();
});


// ===============================
// HELPERS
// ===============================

// Safely parse JSON — if the server returned an HTML error page instead of
// JSON (e.g. a PHP fatal error), this returns a fallback error object rather
// than throwing a SyntaxError that swallows the real problem.
async function safeJson(response) {
    const text = await response.text();
    try {
        return JSON.parse(text);
    } catch {
        // Log the raw response so it's visible in DevTools console
        console.error('Non-JSON response from server:', text);
        return {
            success: false,
            message: 'Server error. Check the browser console for details.',
        };
    }
}


// ===============================
// LOGIN
// ===============================

const loginForm = document.getElementById('loginForm');

loginForm?.addEventListener('submit', async e => {
    e.preventDefault();

    const errorEl  = document.getElementById('loginError');
    errorEl.style.display = 'none';
    errorEl.textContent   = '';

    const submitBtn = loginForm.querySelector('button[type="submit"]');
    submitBtn.disabled    = true;
    submitBtn.textContent = 'Logging in…';

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body:   new FormData(loginForm),
        });

        const result = await safeJson(response);

        if (result.success) {
            window.location.href = 'PAGES/Home_Page.html';
        } else {
            errorEl.style.display = 'block';
            errorEl.textContent   = result.message || 'Login failed. Please try again.';
        }

    } catch (err) {
        errorEl.style.display = 'block';
        errorEl.textContent   = 'Network error. Please check your connection and try again.';
        console.error('Login fetch error:', err);
    } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Login';
    }
});


// ===============================
// SIGNUP AFTER ADMIN APPROVAL
// ===============================

const signupForm = document.getElementById('signupForm');

// admin_code is included in e.detail and sent to register.php for
// server-side validation — it is never checked in the browser.
signupForm?.addEventListener('adminApproved', async e => {
    const userData    = e.detail;
    const signupError = document.getElementById('signupError');
    signupError.style.display = 'none';
    signupError.textContent   = '';

    const formData = new FormData();
    formData.append('full_name',  userData.full_name);
    formData.append('username',   userData.username);
    formData.append('password',   userData.password);
    formData.append('admin_code', userData.admin_code);

    try {
        const response = await fetch('register.php', {
            method: 'POST',
            body:   formData,
        });

        const result = await safeJson(response);

        // Fire adminResult so the modal in Login.html can react (shake vs success)
        signupForm.dispatchEvent(new CustomEvent('adminResult', {
            detail:  result,
            bubbles: true,
        }));

        if (result.success) {
            alert('Account created successfully! You can now log in.');
            wrapper?.classList.remove('active');
            signupForm.reset();
        } else {
            signupError.style.display = 'block';
            signupError.textContent   = result.message || 'Sign up failed. Please try again.';
        }

    } catch (err) {
        const networkResult = { success: false, message: 'Network error. Please try again.' };

        signupForm.dispatchEvent(new CustomEvent('adminResult', {
            detail:  networkResult,
            bubbles: true,
        }));

        signupError.style.display = 'block';
        signupError.textContent   = networkResult.message;
        console.error('Signup fetch error:', err);
    }
});

}); // end DOMContentLoaded
