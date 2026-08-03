// app.js - main ES6 module bootstrap for frontend
import { initTheme } from './theme.js';
import { initNotifications } from './notifications.js';
import { initAOS } from './charts.js';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initNotifications();
    initAOS();

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarCollapse = document.getElementById('sidebar-collapse');
    if (sidebarToggle) sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    if (sidebarCollapse) sidebarCollapse.addEventListener('click', () => sidebar.classList.toggle('open'));

    // Initialize Bootstrap tooltips
    if (window.bootstrap) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
    }
});
