(() => {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('btn');

    if (!sidebar || !toggle) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('bx-menu', !sidebar.classList.contains('open'));
            icon.classList.toggle('bx-menu-alt-right', sidebar.classList.contains('open'));
        }
    });
})();
