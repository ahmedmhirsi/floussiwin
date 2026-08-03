// register.js - client-side validation and AJAX registration using Fetch API (ES module)
if (document.getElementById('register-form')) {
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('register-form');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submit-btn');
        const loader = document.getElementById('submit-loader');

        function showToast(message, type='info'){
            const tc = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.className = 'toast toast-' + type;
            t.textContent = message;
            tc.appendChild(t);
            setTimeout(()=>{ t.classList.add('visible'); }, 10);
            setTimeout(()=>{ t.classList.remove('visible'); setTimeout(()=>tc.removeChild(t),300); }, 5000);
        }

        function setFieldError(id, msg){
            const el = document.getElementById('error-' + id);
            if (el) el.textContent = msg || '';
        }

        function evaluatePassword(pw){
            let score = 0;
            if (pw.length >= 8) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            return score; // 0-4
        }

        passwordInput.addEventListener('input', function(){
            const val = passwordInput.value;
            const score = evaluatePassword(val);
            const meter = document.getElementById('password-strength');
            const desc = document.getElementById('password-desc');
            meter.value = score;
            const texts = ['Très faible','Faible','Moyen','Fort','Très fort'];
            desc.textContent = texts[score];
            setFieldError('password', score < 2 ? 'Mot de passe trop faible' : '');
        });

        let emailCheckTimer = null;
        emailInput.addEventListener('input', function(){
            clearTimeout(emailCheckTimer);
            setFieldError('email', '');
            const email = emailInput.value.trim();
            if (!email) return;
            // basic format check
            const re = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
            if (!re.test(email)) {
                setFieldError('email', 'Format email invalide');
                return;
            }
            // debounce server check
            emailCheckTimer = setTimeout(()=>{
                fetch('index.php?route=check_email&email=' + encodeURIComponent(email), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                }).then(r=>r.json()).then(data=>{
                    if (data && data.exists) {
                        setFieldError('email', 'Cet email est déjà utilisé');
                    }
                }).catch(err=>{
                    console.error('Email check failed', err);
                });
            }, 500);
        });

        form.addEventListener('submit', function(ev){
            ev.preventDefault();
            // clear errors
            setFieldError('name',''); setFieldError('email',''); setFieldError('password','');

            const name = nameInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            const errors = {};
            if (!name) errors.name = 'Le nom est requis.';
            const re = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
            if (!email || !re.test(email)) errors.email = 'Email invalide.';
            const score = evaluatePassword(password);
            if (score < 2) errors.password = 'Mot de passe trop faible (min 8, lettre et chiffre).';

            if (Object.keys(errors).length) {
                Object.keys(errors).forEach(k=>setFieldError(k, errors[k]));
                return;
            }

            // disable UI
            submitBtn.disabled = true; loader.style.display = 'inline-block';

            // read CSRF token from meta
            const meta = document.querySelector('meta[name="csrf-token"]');
            const csrf = meta ? meta.getAttribute('content') : null;

            fetch('index.php?route=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify({ name, email, password, csrf_token: csrf })
            }).then(async res => {
                const data = await res.json().catch(()=>null);
                if (!res.ok) {
                    if (data && data.errors) {
                        Object.entries(data.errors).forEach(([k,v])=>{
                            setFieldError(k, v);
                        });
                    } else {
                        showToast('Erreur serveur. Réessayez.', 'error');
                    }
                    throw new Error('Registration failed');
                }
                if (data && data.success) {
                    showToast('Inscription réussie. Redirection...', 'success');
                    setTimeout(()=>{ window.location.href = data.redirect || 'index.php?route=dashboard'; }, 900);
                } else {
                    showToast('Réponse inattendue du serveur', 'error');
                }
            }).catch(err=>{
                console.error(err);
            }).finally(()=>{
                submitBtn.disabled = false; loader.style.display = 'none';
            });
        });
    });
}

