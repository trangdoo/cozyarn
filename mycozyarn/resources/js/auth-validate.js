/**
 * Realtime regex validation + SHA-256 client-side password hashing.
 *
 * Cách dùng (HTML):
 *   <form data-validate ...>
 *     <input name="email" type="email" data-rule="email" data-required>
 *     <input name="phone" data-rule="phone" data-required>
 *     <input name="password" type="password" data-rule="password" data-required data-hash>
 *     <input name="password_confirmation" type="password" data-match="password" data-required>
 *   </form>
 *
 * `data-rule` chọn 1 trong các pattern bên dưới (giống ValidationPatterns.php).
 * `data-required` để bắt buộc nhập.
 * `data-match="<other_field>"` để kiểm tra trùng (xác nhận mật khẩu).
 * `data-hash` để hash bằng SHA-256 trước khi submit (cho field password).
 *
 * Form sẽ bị chặn submit khi còn lỗi; mỗi field hiện inline error message.
 */

const PATTERNS = {
    /** Họ tên: chữ cái Unicode, khoảng trắng, ., ', - — 2..100 ký tự */
    name: /^[\p{L}][\p{L}\p{M}\s.'-]{1,99}$/u,

    /** Email RFC tương đối — dùng kèm input type=email */
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,

    /** SĐT VN: 0xxxxxxxxx hoặc +84xxxxxxxxx (đầu 3..9), cho phép space/gạch giữa cụm */
    phone: /^(?:\+84|0)[\s\-]?[3-9](?:[\s\-]?\d){8}$/,

    /** Mật khẩu mạnh: tối thiểu 6 ký tự, có cả chữ và số */
    password: /^(?=.*[A-Za-z])(?=.*\d).{6,100}$/,

    /** Slug */
    slug: /^[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?$/,

    /** Đường dẫn ảnh: /img.jpg hoặc URL http(s) */
    image: /^(?:\/[\w\-\.\/]+\.(?:jpe?g|png|webp|gif|svg)(?:\?[\w=&%\-\.]*)?|https?:\/\/[\w\-\.\/?#=&%@:]+)$/i,

    /** Tag */
    tag: /^[\p{L}\p{N}\s-]{1,30}$/u,

    /** Đơn vị sản phẩm */
    unit: /^[\p{L}\p{N}\s.-]{1,30}$/u,
};

const MESSAGES = {
    required: 'Trường này không được để trống.',
    name:     'Họ tên chỉ được chứa chữ cái, khoảng trắng và dấu cách (2–100 ký tự).',
    email:    'Email không hợp lệ.',
    phone:    'Số điện thoại không hợp lệ (vd: 0987654321 hoặc +84987654321).',
    password: 'Mật khẩu phải dài 6–100 ký tự, gồm cả chữ và số.',
    slug:     'Chỉ chữ thường, số và dấu gạch ngang.',
    image:    'Đường dẫn ảnh không hợp lệ.',
    tag:      'Tag chỉ được chứa chữ cái, số và khoảng trắng.',
    unit:     'Đơn vị chỉ chứa chữ và số.',
    match:    'Giá trị nhập lại không khớp.',
};

/* ───────────────────────── SHA-256 helper ───────────────────────── */

const encoder = new TextEncoder();

async function sha256Hex(plaintext) {
    if (!('crypto' in window) || !crypto.subtle) {
        throw new Error('Trình duyệt không hỗ trợ Web Crypto API. Vui lòng dùng HTTPS hoặc nâng cấp trình duyệt.');
    }
    const buffer = await crypto.subtle.digest('SHA-256', encoder.encode(plaintext));
    return Array.from(new Uint8Array(buffer))
        .map((b) => b.toString(16).padStart(2, '0'))
        .join('');
}

/* ───────────────────────── Validation core ───────────────────────── */

/**
 * Validate 1 input. Trả về '' nếu OK, hoặc chuỗi lỗi nếu sai.
 */
function validateField(input, form) {
    const raw = input.value ?? '';
    const value = input.type === 'checkbox' ? input.checked : raw;
    const required = input.hasAttribute('data-required') || input.required;
    const rule = input.dataset.rule;
    const matchName = input.dataset.match;

    if (required && (value === '' || value === false || value == null)) {
        return MESSAGES.required;
    }

    // Trống và không required → coi như OK
    if (!required && raw === '') return '';

    if (matchName) {
        const other = form.querySelector(`[name="${matchName}"]`);
        if (other && other.value !== raw) return MESSAGES.match;
    }

    if (rule && PATTERNS[rule]) {
        if (!PATTERNS[rule].test(raw)) return MESSAGES[rule] || 'Giá trị không hợp lệ.';
    }

    return '';
}

function setFieldState(input, errorMsg) {
    const wrapper = input.closest('.auth-field, .admin-form__field, label, div') || input.parentElement;
    if (!wrapper) return;

    let errorEl = wrapper.querySelector('.field-error');
    if (errorMsg) {
        wrapper.classList.add('is-invalid');
        wrapper.classList.remove('is-valid');
        if (!errorEl) {
            errorEl = document.createElement('small');
            errorEl.className = 'field-error';
            wrapper.appendChild(errorEl);
        }
        errorEl.textContent = errorMsg;
    } else {
        wrapper.classList.remove('is-invalid');
        if (input.value) wrapper.classList.add('is-valid');
        if (errorEl) errorEl.remove();
    }
}

function validateForm(form) {
    const inputs = form.querySelectorAll('input[data-rule], input[data-required], input[data-match], textarea[data-rule], textarea[data-required]');
    let firstInvalid = null;
    let allOk = true;

    inputs.forEach((input) => {
        const err = validateField(input, form);
        setFieldState(input, err);
        if (err) {
            allOk = false;
            firstInvalid ??= input;
        }
    });

    return { allOk, firstInvalid };
}

/* ───────────────────────── Setup & wiring ───────────────────────── */

function attachLiveValidation(form) {
    const debouncers = new WeakMap();
    const inputs = form.querySelectorAll('input, textarea');

    inputs.forEach((input) => {
        const handler = () => {
            const t = debouncers.get(input);
            if (t) clearTimeout(t);
            debouncers.set(input, setTimeout(() => {
                const err = validateField(input, form);
                setFieldState(input, err);

                // Khi password thay đổi → revalidate password_confirmation cùng lúc
                if (input.dataset.rule === 'password') {
                    const confirm = form.querySelector('[data-match="' + input.name + '"]');
                    if (confirm && confirm.value) {
                        setFieldState(confirm, validateField(confirm, form));
                    }
                }
            }, 120));
        };
        input.addEventListener('input', handler);
        input.addEventListener('blur', () => {
            const err = validateField(input, form);
            setFieldState(input, err);
        });
    });
}

async function hashPasswordsBeforeSubmit(form) {
    const fields = form.querySelectorAll('[data-hash]');
    if (fields.length === 0) return;

    // Áp đồng thời cho tất cả field cần hash (password + password_confirmation)
    await Promise.all(Array.from(fields).map(async (field) => {
        const raw = field.value ?? '';
        if (raw === '') return;
        const hex = await sha256Hex(raw);
        field.value = hex;
        // Đánh dấu để debug & ngăn re-hash nếu form bị submit lại
        field.dataset.hashed = '1';
    }));
}

function setupForm(form) {
    if (form.dataset.validateBound === '1') return;
    form.dataset.validateBound = '1';

    attachLiveValidation(form);

    form.addEventListener('submit', async (e) => {
        // Validate đồng bộ trước
        const { allOk, firstInvalid } = validateForm(form);
        if (!allOk) {
            e.preventDefault();
            firstInvalid?.focus();
            return;
        }

        // Nếu form có field cần hash → chặn lại để hash async, rồi submit thủ công
        const needsHash = form.querySelector('[data-hash]:not([data-hashed])');
        if (needsHash) {
            e.preventDefault();
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            try {
                await hashPasswordsBeforeSubmit(form);
                form.submit();
            } catch (err) {
                console.error('[auth-validate] Hash failed:', err);
                alert(err.message || 'Không thể mã hoá mật khẩu trên trình duyệt.');
                if (submitBtn) submitBtn.disabled = false;
            }
        }
    });
}

function init() {
    document.querySelectorAll('form[data-validate]').forEach(setupForm);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Expose để debug và để view khác có thể đăng ký form mới sinh động
window.AuthValidate = {
    setupForm,
    validateForm,
    sha256Hex,
    PATTERNS,
};
