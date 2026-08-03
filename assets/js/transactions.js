// transactions.js - handles CRUD via AJAX, modals, filters, and dashboard updates
import { postJSON } from './ajax.js';
import { showToast } from './notifications.js';

const api = {
    list: 'index.php?route=transactions_list',
    create: 'index.php?route=transactions_create',
    update: 'index.php?route=transactions_update',
    delete: 'index.php?route=transactions_delete',
    summary: 'index.php?route=transactions_summary'
};

let txModal, txForm;

document.addEventListener('DOMContentLoaded', () => {
    txModal = new bootstrap.Modal(document.getElementById('txModal'));
    txForm = document.getElementById('tx-form');

    // init datepicker
    if (window.flatpickr) {
        flatpickr('#tx-date', {dateFormat: 'Y-m-d', defaultDate: new Date()});
        flatpickr('#filter-start', {dateFormat: 'Y-m-d'});
        flatpickr('#filter-end', {dateFormat: 'Y-m-d'});
    }

    // init tom-select for category
    if (window.TomSelect) {
        new TomSelect('#tx-category', {create:false,sortField:{field:'text'}});
        new TomSelect('#filter-category', {create:false});
    }

    document.getElementById('btn-add-transaction').addEventListener('click', openCreateModal);
    document.getElementById('tx-save').addEventListener('click', saveTransaction);
    document.getElementById('btn-filter').addEventListener('click', fetchAndRender);
    document.getElementById('btn-reset').addEventListener('click', resetFilters);

    fetchAndRender();
    updateDashboard();
});

function openCreateModal(){
    clearForm();
    document.getElementById('txModalTitle').textContent = 'Nouvelle transaction';
    txModal.show();
}

function openEditModal(tx){
    document.getElementById('txModalTitle').textContent = 'Modifier la transaction';
    document.getElementById('tx-id').value = tx.id;
    document.getElementById('tx-type').value = tx.type;
    document.getElementById('tx-amount').value = tx.amount;
    document.getElementById('tx-category').value = tx.category;
    document.getElementById('tx-date').value = tx.date;
    document.getElementById('tx-desc').value = tx.description;
    // if Tom Select is used, refresh
    if (window.TomSelect && document.getElementById('tx-category').tomselect) {
        document.getElementById('tx-category').tomselect.setValue(tx.category);
    }
    txModal.show();
}

function clearForm(){
    txForm.reset();
    document.getElementById('tx-id').value = '';
    if (window.TomSelect && document.getElementById('tx-category').tomselect) {
        document.getElementById('tx-category').tomselect.clear();
    }
}

async function saveTransaction(){
    const id = document.getElementById('tx-id').value || null;
    const type = document.getElementById('tx-type').value;
    const amount = parseFloat(document.getElementById('tx-amount').value || 0);
    const category = document.getElementById('tx-category').value || null;
    const date = document.getElementById('tx-date').value;
    const description = document.getElementById('tx-desc').value;

    const payload = { type, amount, category, date, description };
    const btn = document.getElementById('tx-save');
    btn.disabled = true;

    try {
        let res;
        if (id) {
            payload.id = id;
            res = await postJSON(api.update, payload);
        } else {
            res = await postJSON(api.create, payload);
        }
        if (!res.ok) {
            const errors = res.data && res.data.errors;
            if (errors) {
                // show first error
                const msg = Object.values(errors)[0];
                showToast(msg || 'Erreur', 'error');
            } else {
                showToast('Erreur serveur', 'error');
            }
            return;
        }
        showToast(id ? 'Transaction modifiée' : 'Transaction créée', 'success');
        txModal.hide();
        fetchAndRender();
        updateDashboard();
    } catch (e) {
        console.error(e);
        showToast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function fetchAndRender(){
    const q = document.getElementById('filter-q').value.trim();
    const start = document.getElementById('filter-start').value;
    const end = document.getElementById('filter-end').value;
    const category = document.getElementById('filter-category').value;

    const params = new URLSearchParams({route:'transactions_list', q, start, end, category}).toString();
    const fetchUrl = 'index.php?' + params;

    try {
        const r = await fetch(fetchUrl, {headers:{'Accept':'application/json'}});
        if (!r.ok) throw new Error('Fetch failed');
        const json = await r.json();
        if (json && json.success) renderTable(json.data || []);
    } catch (e) {
        console.error(e);
        showToast('Impossible de charger les transactions', 'error');
    }
}

function renderTable(items){
    const tbody = document.getElementById('transactions-table-body');
    tbody.innerHTML = '';
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Aucune transaction</td></tr>';
        return;
    }
    items.forEach(tx=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${escapeHtml(tx.date)}</td>
            <td>${escapeHtml(tx.type)}</td>
            <td>${escapeHtml(tx.category || '')}</td>
            <td>${escapeHtml(tx.description || '')}</td>
            <td class="text-end">${Number(tx.amount).toFixed(2)}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary me-1 btn-edit" data-id="${tx.id}"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${tx.id}"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // attach events
    document.querySelectorAll('.btn-edit').forEach(b=>b.addEventListener('click', async (ev)=>{
        const id = ev.currentTarget.getAttribute('data-id');
        try {
            const r = await fetch('index.php?route=transactions_list&limit=1&q=&id='+encodeURIComponent(id), {headers:{'Accept':'application/json'}});
            // We don't have endpoint for single fetch; reuse list and filter client-side
        } catch(e){/* ignore */}
        // Instead, fetch all and find
        const r2 = await fetch('index.php?route=transactions_list', {headers:{'Accept':'application/json'}});
        const j2 = await r2.json();
        const tx = (j2.data || []).find(x=>String(x.id)===String(id));
        if (tx) openEditModal(tx);
    }));

    document.querySelectorAll('.btn-delete').forEach(b=>b.addEventListener('click', (ev)=>{
        const id = ev.currentTarget.getAttribute('data-id');
        // confirm with SweetAlert2 if available
        if (window.Swal) {
            Swal.fire({title:'Supprimer ?',text:'Cette action est irréversible',icon:'warning',showCancelButton:true,confirmButtonText:'Supprimer',cancelButtonText:'Annuler'}).then(async (res)=>{
                if (res.isConfirmed) await doDelete(id);
            });
        } else if (confirm('Supprimer ?')) {
            doDelete(id);
        }
    }));
}

async function doDelete(id){
    try {
        const res = await postJSON(api.delete, {id});
        if (!res.ok) { showToast('Impossible de supprimer', 'error'); return; }
        showToast('Transaction supprimée', 'success');
        fetchAndRender();
        updateDashboard();
    } catch (e) { console.error(e); showToast('Erreur réseau', 'error'); }
}

async function updateDashboard(){
    try {
        const r = await fetch(api.summary, {headers:{'Accept':'application/json'}});
        if (!r.ok) return;
        const j = await r.json();
        if (!j.success) return;
        const data = j.data;
        // update DOM elements if present
        const incomeEl = document.querySelector('[data-fw="monthly-income"]');
        const expenseEl = document.querySelector('[data-fw="monthly-expense"]');
        const remainEl = document.querySelector('[data-fw="monthly-remaining"]');
        if (incomeEl) incomeEl.textContent = Number(data.income || 0).toFixed(2) + ' DT';
        if (expenseEl) expenseEl.textContent = Number(data.expense || 0).toFixed(2) + ' DT';
        if (remainEl) {
            const rem = (Number(data.income||0) - Number(data.expense||0));
            remainEl.textContent = rem.toFixed(2) + ' DT';
        }
        // update category chart if exists
        if (window.updateCategoryChart) window.updateCategoryChart(data.categories || []);
    } catch (e) { console.error(e); }
}

function resetFilters(){
    document.getElementById('filter-q').value = '';
    document.getElementById('filter-start').value = '';
    document.getElementById('filter-end').value = '';
    const filterCat = document.getElementById('filter-category');
    if (filterCat) {
        if (filterCat.tomselect) filterCat.tomselect.clear();
        else filterCat.value = '';
    }
    fetchAndRender();
}

function escapeHtml(unsafe) {
    if (!unsafe && unsafe !== 0) return '';
    return String(unsafe).replace(/[&<>"]/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m];});
}
