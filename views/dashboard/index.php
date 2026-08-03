<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Top Greeting Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 anim-fade-in-up">
    <div>
        <h2 class="page-title mb-1" style="font-family: var(--font-heading);">Bonjour, <?php echo htmlspecialchars($userName); ?> 👋</h2>
        <p class="page-subtitle mb-0">Ravi de vous revoir sur votre tableau de bord financier.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button id="btn-refresh-dashboard" class="btn-icon" title="Rafraîchir le dashboard">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
        <a href="index.php?route=transactions" class="btn btn-premium btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Nouvelle Transaction
        </a>
    </div>
</div>

<!-- Alert Configuration incomplete -->
<?php if (!$profile): ?>
<div class="alert alert-modern bg-primary bg-opacity-10 border-primary text-primary mb-4 anim-fade-in-up">
    <div class="alert-modern-icon"><i class="bi bi-info-circle-fill"></i></div>
    <div class="flex-1">
        <h6 class="fw-bold mb-1">Configuration incomplète !</h6>
        <p class="small mb-2 text-secondary">Configurez votre profil financier (revenus et charges fixes) pour activer le moteur d'épargne intelligent.</p>
        <a href="index.php?route=profile" class="btn btn-outline-premium btn-sm py-1.5 px-3">
            Configurer mon profil <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Daily Intelligent Recommendation Card -->
<?php if ($dailyRecommendation && $profile): ?>
<div class="rec-box mb-4 anim-fade-in-up">
    <div class="row align-items-center g-3">
        <div class="col-md-7 border-md-end">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-primary text-white p-3 rounded-4 fs-4"><i class="bi bi-lightbulb-fill text-warning"></i></div>
                <div>
                    <h5 class="fw-bold text-dark mb-1" style="font-family: var(--font-heading);">Recommandation du jour</h5>
                    <p class="text-secondary small mb-3">Pour optimiser votre budget et atteindre vos objectifs, épargnez aujourd'hui :</p>
                    <div class="display-6 fw-bold text-primary mb-2" style="font-family: var(--font-heading); font-weight: 700; letter-spacing: -0.03em;">
                        <?php echo number_format($dailyRecommendation['recommended_savings'], 2); ?> <span class="fs-4">DT</span>
                    </div>
                    <span class="badge bg-warning-light text-warning badge-premium text-wrap text-start">
                        💡 <?php echo htmlspecialchars($dailyRecommendation['reason']); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row g-2 px-md-3">
                <div class="col-6 text-center border-end">
                    <span class="text-muted small d-block">Objectif Restant</span>
                    <span class="fw-bold fs-5 text-dark"><?php echo number_format($totalGoalRemaining, 2); ?> DT</span>
                </div>
                <div class="col-6 text-center">
                    <span class="text-muted small d-block">Jours Restants</span>
                    <span class="fw-bold fs-5 text-dark"><?php echo $daysUntilDeadline; ?> jours</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Financial Advice & Insights -->
<?php if (!empty($insights)): ?>
<div class="card border-0 shadow-sm p-4 mb-4 anim-fade-in-up" style="border-radius: var(--radius-lg);">
    <h5 class="fw-bold mb-3" style="font-family: var(--font-heading); font-size: 1.05rem;"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Analyses & Conseils</h5>
    <div class="row g-3">
        <?php foreach ($insights as $insight): ?>
        <?php 
            $typeClass = 'primary';
            $icon = 'info-circle';
            if ($insight['insight_type'] === 'critical') { $typeClass = 'danger'; $icon = 'exclamation-triangle'; }
            elseif ($insight['insight_type'] === 'warning') { $typeClass = 'warning'; $icon = 'exclamation-circle'; }
            elseif ($insight['insight_type'] === 'success') { $typeClass = 'success'; $icon = 'check-circle'; }
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="insight-card">
                <div class="insight-card-icon bg-<?php echo $typeClass; ?>-light text-<?php echo $typeClass; ?>">
                    <i class="bi bi-<?php echo $icon; ?>"></i>
                </div>
                <div class="insight-card-content">
                    <div class="insight-card-title text-<?php echo $typeClass; ?>"><?php echo htmlspecialchars($insight['title']); ?></div>
                    <div class="insight-card-desc"><?php echo htmlspecialchars($insight['message']); ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- 4 Summary Cards Grid -->
<div class="dashboard-grid-1 anim-fade-in-up">
    <!-- Card 1: Income -->
    <div class="stat-card income">
        <div class="stat-card-header">
            <span class="stat-card-label">Revenu ce mois</span>
            <div class="stat-card-icon income"><i class="bi bi-arrow-down-left-circle"></i></div>
        </div>
        <div class="stat-card-value text-success" data-fw="monthly-income">
            <?php echo number_format($monthlyIncome, 2); ?> DT
        </div>
        <div class="stat-card-footer">
            <i class="bi bi-calendar-event"></i>
            <span>Mois en cours</span>
        </div>
    </div>

    <!-- Card 2: Expenses -->
    <div class="stat-card expenses">
        <div class="stat-card-header">
            <span class="stat-card-label">Dépenses ce mois</span>
            <div class="stat-card-icon expenses"><i class="bi bi-arrow-up-right-circle"></i></div>
        </div>
        <div class="stat-card-value text-danger" data-fw="monthly-expense">
            <?php echo number_format($monthlyExpenses, 2); ?> DT
        </div>
        <div class="stat-card-footer">
            <span class="text-danger fw-semibold">
                Moyenne : <?php echo number_format($dailyBudget, 2); ?> DT/jour
            </span>
        </div>
    </div>

    <!-- Card 3: Balance -->
    <div class="stat-card balance">
        <div class="stat-card-header">
            <span class="stat-card-label">Reste disponible</span>
            <div class="stat-card-icon balance"><i class="bi bi-cash-stack"></i></div>
        </div>
        <div class="stat-card-value text-primary" data-fw="monthly-remaining">
            <?php echo number_format($remaining, 2); ?> DT
        </div>
        <div class="stat-card-footer">
            <span>Budget quotidien recommandé</span>
        </div>
    </div>

    <!-- Card 4: Savings Rate -->
    <div class="stat-card savings">
        <div class="stat-card-header">
            <span class="stat-card-label">Taux d'épargne</span>
            <div class="stat-card-icon savings"><i class="bi bi-piggy-bank"></i></div>
        </div>
        <div class="stat-card-value text-warning" data-fw="saving-rate">
            <?php echo number_format($savingRate ?? 0, 2); ?>%
        </div>
        <div class="stat-card-footer flex-column align-items-start gap-1 w-100 mt-2">
            <div class="progress w-100" style="height: 6px; background-color: var(--border);">
                <div id="saving-progress" class="progress-bar bg-success" role="progressbar" style="width: <?php echo number_format($savingRate ?? 0, 2); ?>%" aria-valuenow="<?php echo number_format($savingRate ?? 0, 2); ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Summary Row -->
<div class="dashboard-row-charts anim-fade-in-up">
    <!-- Doughnut Category Chart -->
    <div class="chart-card">
        <div class="chart-card-header">
            <h5 class="chart-card-title"><i class="bi bi-pie-chart me-2 text-primary"></i>Dépenses par catégorie (Ce mois)</h5>
            <span class="badge bg-primary-light text-primary badge-premium">Chart.js</span>
        </div>
        <div class="chart-canvas-wrapper d-flex align-items-center justify-content-center">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Right Summary Panel -->
    <div class="d-flex flex-column gap-3">
        <!-- mini goals panel -->
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="font-family: var(--font-heading);"><i class="bi bi-bullseye me-2 text-primary"></i>Objectifs actifs</h6>
                <a href="index.php?route=goals" class="text-decoration-none text-primary small fw-semibold">Tous <i class="bi bi-chevron-right"></i></a>
            </div>
            
            <?php if (empty($goals)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-flag-fill fs-2 mb-2 text-muted opacity-50"></i>
                    <p class="small mb-0">Aucun objectif actif</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach (array_slice($goals, 0, 3) as $g):
                        $percent = $g['target_amount'] > 0 ? round($g['saved_amount'] / $g['target_amount'] * 100, 1) : 0;
                    ?>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-dark"><?php echo htmlspecialchars($g['name']); ?></span>
                                <span class="small text-muted"><?php echo $percent; ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--border);">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min($percent, 100); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Actions panel -->
        <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-lg);">
            <h6 class="fw-bold mb-3" style="font-family: var(--font-heading);"><i class="bi bi-sliders2-vertical me-2 text-primary"></i>Actions rapides</h6>
            <div class="d-flex flex-column gap-2">
                <a href="index.php?route=saving_challenge" class="btn btn-outline-premium btn-sm py-2">
                    <i class="bi bi-calendar-check me-2"></i>Smart Saving Board
                </a>
                <button id="quick-add" class="btn btn-premium btn-sm py-2">
                    <i class="bi bi-plus-lg me-2"></i>Ajouter Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions List Panel -->
<div class="card border-0 shadow-sm p-4 mb-4 anim-fade-in-up" style="border-radius: var(--radius-lg);">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0" style="font-family: var(--font-heading);"><i class="bi bi-clock-history me-2 text-primary"></i>Transactions récentes</h5>
        <a href="index.php?route=transactions" class="btn btn-outline-premium btn-sm py-1.5 px-3">
            Voir tout <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>

    <div class="activity-list">
        <?php if (!empty($initialTransactions)): ?>
            <?php foreach (array_slice($initialTransactions, 0, 5) as $tx): ?>
                <?php 
                    $typeClass = $tx['type'] === 'income' ? 'income' : 'expense';
                    $sign = $tx['type'] === 'income' ? '+' : '-';
                    $icon = $tx['type'] === 'income' ? 'bi-arrow-down-left-circle-fill' : 'bi-cart-fill';
                    $bgClass = $tx['type'] === 'income' ? 'success' : 'danger';
                    
                    // Specific category icon mapping
                    $category = $tx['category'] ?? '';
                    if (strpos($category, 'Nourriture') !== false || strpos($category, 'Café') !== false) {
                        $icon = 'bi-cup-hot-fill';
                    } elseif (strpos($category, 'Transport') !== false) {
                        $icon = 'bi-car-front-fill';
                    } elseif (strpos($category, 'Loyer') !== false || strpos($category, 'Factures') !== false) {
                        $icon = 'bi-house-fill';
                    } elseif (strpos($category, 'Internet') !== false) {
                        $icon = 'bi-wifi';
                    } elseif (strpos($category, 'Santé') !== false) {
                        $icon = 'bi-heart-pulse-fill';
                    }
                ?>
                <div class="activity-item stagger-item">
                    <div class="activity-details">
                        <div class="activity-category-icon bg-<?php echo $bgClass; ?>-light text-<?php echo $bgClass; ?>">
                            <i class="bi <?php echo $icon; ?>"></i>
                        </div>
                        <div>
                            <div class="activity-desc"><?php echo htmlspecialchars($tx['description'] ?: $tx['category']); ?></div>
                            <div class="activity-meta"><?php echo htmlspecialchars($tx['category']); ?> • <?php echo date('d/m/Y', strtotime($tx['date'])); ?></div>
                        </div>
                    </div>
                    <div class="activity-amount <?php echo $typeClass; ?>">
                        <?php echo $sign; ?> <?php echo number_format($tx['amount'], 2); ?> DT
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4 text-muted small">
                Aucune transaction récente enregistrée.
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="module" src="assets/js/dashboard.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
