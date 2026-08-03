<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container py-5 anim-fade-in-up">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Rapports financiers</h1>
            <p class="page-subtitle mb-0">Visualisez vos dépenses, revenus et tendances récentes en un seul endroit.</p>
        </div>
        <a href="index.php?route=dashboard" class="btn btn-outline-premium btn-sm">Retour au dashboard</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-lg);">
                <h5 class="fw-bold mb-3">Rapport de dépenses</h5>
                <p class="text-secondary small">Analyse vos catégories de dépenses avec des graphiques clairs.</p>
                <div class="chart-canvas-wrapper mt-4" style="min-height: 260px;">
                    <canvas id="reportExpensesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-lg);">
                <h5 class="fw-bold mb-3">Tendance de revenus</h5>
                <p class="text-secondary small">Suivez l'évolution de vos revenus et de votre épargne sur plusieurs périodes.</p>
                <div class="chart-canvas-wrapper mt-4" style="min-height: 260px;">
                    <canvas id="reportIncomeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-3">
        <div class="col-lg-4">
            <div class="stat-card income">
                <span class="stat-card-label">Revenu total</span>
                <div class="stat-card-value text-success">-- DT</div>
                <div class="stat-card-footer">Dernier mois</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card expenses">
                <span class="stat-card-label">Dépenses totales</span>
                <div class="stat-card-value text-danger">-- DT</div>
                <div class="stat-card-footer">Dernier mois</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card balance">
                <span class="stat-card-label">Épargne estimée</span>
                <div class="stat-card-value text-primary">-- DT</div>
                <div class="stat-card-footer">Prochain rapport</div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
