// goals.js - handles goals CRUD via AJAX, modals, and savings management
import { postJSON } from './ajax.js';
import { showToast } from './notifications.js';

const api = {
    list: 'index.php?route=goals_list',
    create: 'index.php?route=goals_create',
    update: 'index.php?route=goals_update',
    delete: 'index.php?route=goals_delete',
    addSavings: 'index.php?route=goals_add_savings'
};

let goalModal, goalForm, savingsModal, savingsForm;

document.addEventListener('DOMContentLoaded', () => {
    goalModal = new bootstrap.Modal(document.getElementById('goalModal'));
    goalForm = document.getElementById('goal-form');
    savingsModal = new bootstrap.Modal(document.getElementById('savingsModal'));
    savingsForm = document.getElementById('savings-form');

    // init datepicker
    if (window.flatpickr) {
        flatpickr('#goal-deadline', {dateFormat: 'Y-m-d', minDate: 'today'});
    }

    document.getElementById('btn-add-goal').addEventListener('click', openCreateModal);
    document.getElementById('goal-save').addEventListener('click', saveGoal);
    document.getElementById('savings-save').addEventListener('click', addSavings);

    // Edit goal buttons
    document.querySelectorAll('.btn-edit-goal').forEach(b => {
        b.addEventListener('click', (ev) => {
            const id = ev.currentTarget.getAttribute('data-id');
            openEditModal(id);
        });
    });

    // Delete goal buttons
    document.querySelectorAll('.btn-delete-goal').forEach(b => {
        b.addEventListener('click', (ev) => {
            const id = ev.currentTarget.getAttribute('data-id');
            confirmDelete(id);
        });
    });

    // Add savings buttons
    document.querySelectorAll('.btn-add-savings').forEach(b => {
        b.addEventListener('click', (ev) => {
            const id = ev.currentTarget.getAttribute('data-id');
            const saved = ev.currentTarget.getAttribute('data-saved');
            openSavingsModal(id, saved);
        });
    });
});

function openCreateModal() {
    clearGoalForm();
    document.getElementById('goalModalTitle').textContent = 'Nouvel objectif';
    goalModal.show();
}

function openEditModal(id) {
    // Fetch goal data from server
    fetchGoals().then(goals => {
        const goal = goals.find(g => String(g.id) === String(id));
        if (goal) {
            document.getElementById('goalModalTitle').textContent = 'Modifier l\'objectif';
            document.getElementById('goal-id').value = goal.id;
            document.getElementById('goal-name').value = goal.name;
            document.getElementById('goal-target').value = goal.target_amount;
            document.getElementById('goal-saved').value = goal.saved_amount;
            document.getElementById('goal-deadline').value = goal.deadline || '';
            goalModal.show();
        } else {
            showToast('Objectif introuvable', 'error');
        }
    }).catch(err => {
        console.error(err);
        showToast('Erreur de chargement', 'error');
    });
}

function clearGoalForm() {
    goalForm.reset();
    document.getElementById('goal-id').value = '';
}

async function saveGoal() {
    const id = document.getElementById('goal-id').value || null;
    const name = document.getElementById('goal-name').value.trim();
    const targetAmount = parseFloat(document.getElementById('goal-target').value || 0);
    const savedAmount = parseFloat(document.getElementById('goal-saved').value || 0);
    const deadline = document.getElementById('goal-deadline').value;

    const errors = {};
    if (!name) errors.name = 'Le nom est requis.';
    if (!targetAmount || targetAmount <= 0) errors.target_amount = 'Le montant cible doit être positif.';
    if (savedAmount < 0) errors.saved_amount = 'Le montant épargné ne peut pas être négatif.';

    if (Object.keys(errors).length) {
        const msg = Object.values(errors)[0];
        showToast(msg, 'error');
        return;
    }

    const payload = { name, target_amount: targetAmount, saved_amount: savedAmount, deadline };
    if (id) payload.id = id;

    const btn = document.getElementById('goal-save');
    btn.disabled = true;

    try {
        // Note: These routes don't exist yet in the controller - will need to be added
        const url = id ? api.update : api.create;
        const res = await postJSON(url, payload);
        
        if (!res.ok) {
            const errors = res.data && res.data.errors;
            if (errors) {
                const msg = Object.values(errors)[0];
                showToast(msg || 'Erreur', 'error');
            } else {
                showToast('Erreur serveur', 'error');
            }
            return;
        }
        
        showToast(id ? 'Objectif modifié' : 'Objectif créé', 'success');
        goalModal.hide();
        location.reload(); // Reload to show updated goals
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
    }
}

function openSavingsModal(goalId, currentSaved) {
    document.getElementById('savings-goal-id').value = goalId;
    document.getElementById('savings-amount').value = '';
    savingsModal.show();
}

async function addSavings() {
    const goalId = document.getElementById('savings-goal-id').value;
    const amount = parseFloat(document.getElementById('savings-amount').value || 0);

    if (!amount || amount <= 0) {
        showToast('Le montant doit être positif', 'error');
        return;
    }

    const btn = document.getElementById('savings-save');
    btn.disabled = true;

    try {
        // Note: This route doesn't exist yet - will need to be added
        const res = await postJSON(api.addSavings, { goal_id: goalId, amount });
        
        if (!res.ok) {
            showToast('Erreur lors de l\'ajout', 'error');
            return;
        }
        
        showToast('Épargne ajoutée avec succès', 'success');
        savingsModal.hide();
        location.reload();
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
    }
}

function confirmDelete(id) {
    if (window.Swal) {
        Swal.fire({
            title: 'Supprimer l\'objectif ?',
            text: 'Cette action est irréversible',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (res) => {
            if (res.isConfirmed) await doDelete(id);
        });
    } else if (confirm('Supprimer cet objectif ?')) {
        doDelete(id);
    }
}

async function doDelete(id) {
    try {
        // Note: This route doesn't exist yet - will need to be added
        const res = await postJSON(api.delete, { id });
        if (!res.ok) {
            showToast('Impossible de supprimer', 'error');
            return;
        }
        showToast('Objectif supprimé', 'success');
        location.reload();
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    }
}

async function fetchGoals() {
    try {
        const r = await fetch(api.list, { headers: { 'Accept': 'application/json' } });
        if (!r.ok) throw new Error('Fetch failed');
        const json = await r.json();
        return json.data || [];
    } catch (e) {
        console.error(e);
        return [];
    }
}
