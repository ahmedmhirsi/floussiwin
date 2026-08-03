// theme.js - handles light/dark theme toggle
export function initTheme(){
    const body = document.body;
    const toggle = document.getElementById('theme-toggle');
    // read from localStorage
    const saved = localStorage.getItem('fw_theme');
    if (saved === 'dark') {
        body.classList.add('dark');
        document.documentElement.classList.add('dark');
    }

    if (!toggle) return;
    toggle.addEventListener('click', () => {
        const isDark = body.classList.toggle('dark');
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('fw_theme', isDark ? 'dark' : 'light');
        // change icon
        toggle.innerHTML = isDark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
    });
}
