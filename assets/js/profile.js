// profile.js - handles profile configuration and fixed expenses
import { postJSON } from './ajax.js';
import { showToast } from './notifications.js';

const api = {
    saveProfile: 'index.php?route=profile_save',
    addExpense: 'index.php?route=profile_add_expense',
    updateExpense: 'index.php?route=profile_update_expense',
    deleteExpense: 'index.php?route=profile_delete_expense'
};

let expenseModal, expenseForm;

document.addEventListener('DOMContentLoaded', () => {
    expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
    expenseForm = document.getElementById('expense-form');

    // Profile form
    document.getElementById('save-profile').addEventListener('click', saveProfile);
    
    // Update total income display when inputs change
    document.getElementById('monthly-salary').addEventListener('input', updateTotalIncome);
    document.getElementById('additional-income').addEventListener('input', updateTotalIncome);

    // Fixed expenses
    document.getElementById('add-fixed-expense').addEventListener('click', openExpenseModal);
    document.getElementById('save-expense').addEventListener('click', saveExpense);

    // Edit/Delete expense buttons
    document.querySelectorAll('.btn-edit-expense').forEach(b => {
        b.addEventListener('click', (ev) => {
            const id = ev.currentTarget.getAttribute('data-id');
            editExpense(id);
        });
    });

    document.querySelectorAll('.btn-delete-expense').forEach(b => {
        b.addEventListener('click', (ev) => {
            const id = ev.currentTarget.getAttribute('data-id');
            deleteExpense(id);
        });
    });
});

function updateTotalIncome() {
    const salary = parseFloat(document.getElementById('monthly-salary').value) || 0;
    const additional = parseFloat(document.getElementById('additional-income').value) || 0;
    const total = salary + additional;
    document.getElementById('total-income-display').textContent = total.toFixed(2) + ' DT';
}

async function saveProfile() {
    const monthlySalary = parseFloat(document.getElementById('monthly-salary').value) || 0;
    const additionalIncome = parseFloat(document.getElementById('additional-income').value) || 0;

    const errors = {};
    if (monthlySalary < 0) errors.monthly_salary = 'Salaire invalide';
    if (additionalIncome < 0) errors.additional_income = 'Revenus invalides';

    if (Object.keys(errors).length) {
        const msg = Object.values(errors)[0];
        showToast(msg, 'error');
        return;
    }

    const btn = document.getElementById('save-profile');
    btn.disabled = true;

    try {
        const res = await postJSON(api.saveProfile, {
            monthly_salary: monthlySalary,
            additional_income: additionalIncome
        });

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

        showToast('Profil enregistré avec succès', 'success');
        updateAvailableAfterFixed();
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
    }
}

function openExpenseModal() {
    clearExpenseForm();
    document.getElementById('expenseModalTitle').textContent = 'Nouvelle charge fixe';
    expenseModal.show();
}

function editExpense(id) {
    // Find expense data from the DOM
    const expenseEl = document.querySelector(`.btn-edit-expense[data-id="${id}"]`).closest('.d-flex');
    const name = expenseEl.querySelector('strong').textContent;
    const amountText = expenseEl.querySelector('.fw-bold').textContent;
    const amount = parseFloat(amountText.replace(' DT', ''));

    document.getElementById('expenseModalTitle').textContent = 'Modifier la charge fixe';
    document.getElementById('expense-id').value = id;
    document.getElementById('expense-name').value = name;
    document.getElementById('expense-amount').value = amount;
    expenseModal.show();
}

function clearExpenseForm() {
    expenseForm.reset();
    document.getElementById('expense-id').value = '';
}

async function saveExpense() {
    const id = document.getElementById('expense-id').value || null;
    const name = document.getElementById('expense-name').value.trim();
    const amount = parseFloat(document.getElementById('expense-amount').value) || 0;
    const category = document.getElementById('expense-category').value || null;

    const errors = {};
    if (!name) errors.name = 'Le nom est requis';
    if (!amount || amount <= 0) errors.amount = 'Le montant doit être positif';

    if (Object.keys(errors).length) {
        const msg = Object.values(errors)[0];
        showToast(msg, 'error');
        return;
    }

    const btn = document.getElementById('save-expense');
    btn.disabled = true;

    try {
        const url = id ? api.updateExpense : api.addExpense;
        const payload = { name, amount, category };
        if (id) payload.id = id;

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

        showToast(id ? 'Charge modifiée' : 'Charge ajoutée', 'success');
        expenseModal.hide();
        location.reload();
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
    }
}

function deleteExpense(id) {
    if (confirm('Supprimer cette charge fixe ?')) {
        doDeleteExpense(id);
    }
}

async function doDeleteExpense(id) {
    try {
        const res = await postJSON(api.deleteExpense, { id });
        if (!res.ok) {
            showToast('Impossible de supprimer', 'error');
            return;
        }
        showToast('Charge supprimée', 'success');
        location.reload();
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    }
}

function updateAvailableAfterFixed() {
    const totalIncome = parseFloat(document.getElementById('total-income-display').textContent) || 0;
    const totalFixed = parseFloat(document.getElementById('total-fixed-expenses').textContent) || 0;
    const available = Math.max(0, totalIncome - totalFixed);
    document.getElementById('available-after-fixed').textContent = available.toFixed(2) + ' DT';
}
