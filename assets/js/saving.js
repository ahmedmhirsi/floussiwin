import { showToast } from './notifications.js';

function updateProgress() {
    const goalEl = document.getElementById('stat-goal');
    const savedEl = document.getElementById('stat-saved');
    const remainingEl = document.getElementById('stat-remaining');
    const progressBar = document.getElementById('saving-board-progress');
    const progressBadge = document.getElementById('saving-progress-badge');

    const goal = goalEl ? parseFloat(goalEl.textContent.replace(/[^0-9.-]+/g, '')) : 0;
    const saved = savedEl ? parseFloat(savedEl.textContent.replace(/[^0-9.-]+/g, '')) : 0;
    const percent = goal > 0 ? Math.round((saved / goal) * 100) : 0;

    if (progressBar) {
        progressBar.style.width = percent + '%';
        progressBar.setAttribute('aria-valuenow', String(percent));
        progressBar.textContent = percent + '%';
    }
    if (progressBadge) {
        progressBadge.textContent = `Progression ${percent}%`;
    }
}

function handleCellClick(cell) {
    const isCompleted = cell.classList.contains('completed');
    const amount = parseFloat(cell.getAttribute('data-amount')) || 0;
    const savedEl = document.getElementById('stat-saved');
    const remainingEl = document.getElementById('stat-remaining');
    const goalEl = document.getElementById('stat-goal');

    let saved = savedEl ? parseFloat(savedEl.textContent.replace(/[^0-9.-]+/g, '')) : 0;
    let remaining = remainingEl ? parseFloat(remainingEl.textContent.replace(/[^0-9.-]+/g, '')) : 0;

    if (isCompleted) {
        cell.classList.remove('completed');
        saved = Math.max(0, saved - amount);
        remaining = remaining + amount;
        showToast('Cellule décochée', 'info');
    } else {
        cell.classList.add('completed');
        saved = saved + amount;
        remaining = Math.max(0, remaining - amount);
        showToast('Jour marqué comme complété', 'success');
    }

    if (savedEl) savedEl.textContent = saved.toFixed(2) + ' DT';
    if (remainingEl) remainingEl.textContent = remaining.toFixed(2) + ' DT';

    // persist state per month
    const monthKey = getMonthKey();
    const day = cell.getAttribute('data-day');
    let stored = JSON.parse(localStorage.getItem(monthKey) || '[]');
    if (!isCompleted) {
        // add
        if (!stored.includes(day)) stored.push(day);
    } else {
        // remove
        stored = stored.filter(d => d !== day);
    }
    localStorage.setItem(monthKey, JSON.stringify(stored));

    updateProgress();
}

function getMonthKey() {
    const monthLabelEl = document.querySelector('.saving-board-grid').closest('div.card').querySelector('.badge');
    const month = monthLabelEl ? monthLabelEl.textContent.trim() : new Date().toISOString().slice(0,7);
    return 'saving_completed_' + month.replace(/\s+/g, '_');
}

document.addEventListener('DOMContentLoaded', () => {
    const cells = document.querySelectorAll('.saving-cell');
    // restore persisted completed days
    const monthKey = getMonthKey();
    const stored = JSON.parse(localStorage.getItem(monthKey) || '[]');
    cells.forEach(cell => {
        const day = cell.getAttribute('data-day');
        if (stored.includes(day)) {
            cell.classList.add('completed');
        }
        cell.addEventListener('click', () => handleCellClick(cell));
    });
    updateProgress();
});
