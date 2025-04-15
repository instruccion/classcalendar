// resources/js/layout.js

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main-content');
    const overlay = document.getElementById('sidebar-overlay');
    const mobileToggleBtn = document.getElementById('menu-toggle');
    const desktopToggleBtn = document.getElementById('desktop-menu-toggle');
    const avatarBtn = document.getElementById('avatar-btn');
    const avatarMenu = document.getElementById('avatar-menu');
    const fullscreenBtn = document.getElementById('btnFullscreen');
    const body = document.body;
    const htmlElement = document.documentElement;

    const openMobileSidebar = () => {
        sidebar?.classList.remove('hidden', '-translate-x-full');
        overlay?.classList.remove('hidden');
        setTimeout(() => overlay?.classList.remove('opacity-0'), 10);
        body.classList.add('overflow-hidden');
    };

    const closeMobileSidebar = () => {
        overlay?.classList.add('opacity-0');
        sidebar?.classList.add('-translate-x-full');
        body.classList.remove('overflow-hidden');
        setTimeout(() => {
            sidebar?.classList.add('hidden');
            overlay?.classList.add('hidden');
        }, 300);
    };

    const toggleDesktopSidebar = () => {
        body.classList.toggle('sidebar-collapsed');
    };

    mobileToggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (sidebar?.classList.contains('hidden')) {
            openMobileSidebar();
        } else {
            closeMobileSidebar();
        }
    });

    desktopToggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDesktopSidebar();
    });

    overlay?.addEventListener('click', closeMobileSidebar);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && window.innerWidth < 1024 && !sidebar?.classList.contains('hidden')) {
            closeMobileSidebar();
        }
    });

    avatarBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        avatarMenu?.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (avatarMenu && !avatarMenu.classList.contains('hidden') &&
            !avatarBtn?.contains(e.target) && !avatarMenu.contains(e.target)) {
            avatarMenu.classList.add('hidden');
        }
    });

    fullscreenBtn?.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            htmlElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });

    const handleResize = () => {
        if (window.innerWidth >= 1024) {
            closeMobileSidebar();
            if (!body.classList.contains('sidebar-collapsed')) {
                sidebar?.classList.remove('hidden', '-translate-x-full');
            } else {
                sidebar?.classList.add('-translate-x-full');
            }
        }
    };

    window.addEventListener('resize', handleResize);
    handleResize();
});
