// dashboard.js - fetch summary and render category chart + wire quick actions
import { createPieChart } from './charts.js';
import { showToast } from './notifications.js';

async function fetchSummary(){
    try{
        const r = await fetch('index.php?route=transactions_summary', {headers:{'Accept':'application/json'}});
        if (!r.ok) throw new Error('Failed to load summary');
        const j = await r.json();
        if (!j.success) throw new Error('Invalid response');
        return j.data;
    } catch(e){
        console.error(e);
        // Only show toast if we're on the dashboard page (not login/register)
        if (document.querySelector('[data-fw="monthly-income"]')) {
            showToast('Impossible de charger le résumé', 'error');
        }
        return null;
    }
}

let categoryChart = null;

export async function initDashboard(){
    const data = await fetchSummary();
    if (!data) return;

    // Update stats
    const incomeEl = document.querySelector('[data-fw="monthly-income"]');
    const expenseEl = document.querySelector('[data-fw="monthly-expense"]');
    const remainEl = document.querySelector('[data-fw="monthly-remaining"]');
    const savingEl = document.querySelector('[data-fw="saving-rate"]');

    if (incomeEl) incomeEl.textContent = Number(data.income||0).toFixed(2) + ' DT';
    if (expenseEl) expenseEl.textContent = Number(data.expense||0).toFixed(2) + ' DT';
    if (remainEl) {
        const rem = (Number(data.income||0) - Number(data.expense||0));
        remainEl.textContent = rem.toFixed(2) + ' DT';
    }
    if (savingEl) {
        // approximate saving rate
        const income = Number(data.income||0);
        const expense = Number(data.expense||0);
        const rate = income > 0 ? Math.max(0, ((income - expense) / income) * 100) : 0;
        savingEl.textContent = rate.toFixed(2) + '%';
        const bar = document.getElementById('saving-progress'); if (bar) { bar.style.width = rate.toFixed(2) + '%'; bar.setAttribute('aria-valuenow', rate.toFixed(2)); }
    }

    // Categories chart
    const categories = data.categories || [];
    const labels = categories.map(c=>c.category || 'Autres');
    const values = categories.map(c=>Number(c.total));

    const ctx = document.getElementById('categoryChart');
    if (ctx && window.Chart) {
        const chartData = {
            labels: labels,
            datasets: [{ data: values, backgroundColor: generateColors(values.length) }]
        };
        if (categoryChart) { categoryChart.data = chartData; categoryChart.update(); }
        else categoryChart = createPieChart(ctx, chartData, {responsive:true, maintainAspectRatio:false});
    }
}

function generateColors(n){
    const palette = ['#2563EB','#22C55E','#F59E0B','#EF4444','#60A5FA','#34D399','#FBBF24','#FB7185','#A78BFA','#94A3B8'];
    const out = [];
    for (let i=0;i<n;i++) out.push(palette[i % palette.length]);
    return out;
}

// init on DOM ready
document.addEventListener('DOMContentLoaded', ()=>{
    initDashboard();
    const refresh = document.getElementById('btn-refresh-dashboard');
    if (refresh) refresh.addEventListener('click', ()=>{ initDashboard(); showToast('Dashboard rafraîchi', 'success'); });

    const quick = document.getElementById('quick-add');
    if (quick) quick.addEventListener('click', ()=>{ window.location.href = 'index.php?route=transactions'; });
});
