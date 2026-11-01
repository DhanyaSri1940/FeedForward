// Sidebar and Dark Mode Toggle Script

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const modeToggle = body.querySelector('.mode-toggle');
    const sidebar = body.querySelector('nav');
    const sidebarToggle = body.querySelector('.sidebar-toggle');

    // Restore mode from localStorage
    const savedMode = localStorage.getItem('mode');
    if (savedMode === 'dark') {
        body.classList.add('dark');
    }

    // Restore sidebar status from localStorage
    const savedStatus = localStorage.getItem('status');
    if (savedStatus === 'close') {
        sidebar.classList.add('close');
    }

    // Toggle dark mode
    modeToggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('mode', body.classList.contains('dark') ? 'dark' : 'light');
    });

    // Toggle sidebar
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('close');
        localStorage.setItem('status', sidebar.classList.contains('close') ? 'close' : 'open');
    });
}); 