import { showToast } from './notifications.js';

function updateProgress() {
    const cells = Array.from(document.querySelectorAll('.saving-cell'));
    const completed = cells.filter(cell => cell.classList.contains('completed')).length;
    const percent = cells.length ? Math.round((completed / cells.length) * 100) : 0;
    const progressBar = document.getElementById('saving-board-progress');
    if (progressBar) {
        progressBar.style.width = percent + '%';
        progressBar.setAttribute('aria-valuenow', String(percent));
        progressBar.textContent = percent + '%';
    }
}

function handleCellClick(cell) {
    const isCompleted = cell.classList.contains('completed');
    if (isCompleted) {
        cell.classList.remove('completed');
        showToast('Cellule décochée', 'info');
    } else {
        cell.classList.add('completed');
        showToast('Jour marqué comme complété', 'success');
    }
    updateProgress();
}

document.addEventListener('DOMContentLoaded', () => {
    const cells = document.querySelectorAll('.saving-cell');
    cells.forEach(cell => {
        cell.addEventListener('click', () => handleCellClick(cell));
    });
    updateProgress();
});
