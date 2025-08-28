// Основной JavaScript код для админ-панели
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    document.querySelectorAll('.alert-dismissible.fade.show:not(.alert-important)').forEach(function(alert) {
        setTimeout(() => {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Theme switcher
    const themeSwitch = document.getElementById('themeSwitch');
    if (themeSwitch) {
        const html = document.documentElement;
        const sunIcon = themeSwitch.querySelector('.bi-sun-fill');
        const moonIcon = themeSwitch.querySelector('.bi-moon-fill');

        // Check saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            html.setAttribute('data-bs-theme', 'light');
            if (sunIcon) sunIcon.classList.remove('d-none');
            if (moonIcon) moonIcon.classList.add('d-none');
        }

        // Theme switch handler
        themeSwitch.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            if (newTheme === 'light') {
                if (sunIcon) sunIcon.classList.remove('d-none');
                if (moonIcon) moonIcon.classList.add('d-none');
            } else {
                if (sunIcon) sunIcon.classList.add('d-none');
                if (moonIcon) moonIcon.classList.remove('d-none');
            }
        });
    }
});
