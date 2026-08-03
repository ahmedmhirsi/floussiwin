<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="page-header anim-fade-in-up">
    <div>
        <span class="badge bg-primary-light text-primary badge-premium mb-3">Aujourd'hui</span>
        <h1 class="page-title mb-1" style="font-family: var(--font-heading);">Bonjour <?php echo htmlspecialchars($userName); ?> 👋</h1>
        <p class="page-subtitle mb-0">Votre copilote financier vous guide pour prendre la meilleure décision aujourd'hui.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
        <a href="index.php?route=transactions" class="btn btn-premium btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Ajouter transaction
        </a>
        <a href="index.php?route=saving" class="btn btn-outline-premium btn-sm">
            <i class="bi bi-piggy-bank-fill me-1"></i>Plan d'épargne intelligent
        </a>
    </div>
</div>

<?php if (!$profile): ?>
<div class="alert alert-modern bg-primary bg-opacity-10 border-primary text-primary mb-4 anim-fade-in-up">
    <div class="alert-modern-icon"><i class="bi bi-info-circle-fill"></i></div>
    <div class="flex-1">
        <h6 class="fw-bold mb-1">Profil financier incomplet</h6>
        <p class="small mb-2 text-secondary">Renseignez vos revenus et charges fixes pour activer l'analyse intelligente et recevoir des recommandations personnalisées.</p>
        <a href="index.php?route=profile" class="btn btn-outline-premium btn-sm py-1.5 px-3">
            Compléter mon profil <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4 anim-fade-in-up">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="fw-bold mb-1">Budget disponible aujourd'hui</h4>
                    <p class="text-secondary small mb-0">Cashflow disponible pour vos dépenses et épargne.</p>
                </div>
                <span class="badge bg-success-light text-success">Aujourd'hui</span>
            </div>
            <div class="display-5 fw-bold text-primary mb-3"><?php echo number_format($budgetSummary['availableToday'] ?? 0, 2); ?> DT</div>
            <div class="d-flex flex-wrap gap-3 text-muted small">
                <div><strong><?php echo $budgetSummary['remainingDays'] ?? 0; ?> jours</strong> restants</div>
                <div><strong><?php echo number_format($budgetSummary['monthlyRemaining'] ?? 0, 2); ?> DT</strong> total disponible</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="fw-bold mb-1">Mission du jour</h4>
                    <p class="text-secondary small mb-0">Une action simple et réaliste pour avancer.</p>
                </div>
                <span class="badge bg-warning-light text-warning">Action</span>
            </div>
            <h3 class="fw-bold mb-2"><?php echo htmlspecialchars($mission['title'] ?? '—'); ?></h3>
            <p class="text-dark mb-2"><?php echo htmlspecialchars($mission['description'] ?? '—'); ?></p>
            <p class="text-muted small mb-0"><?php echo htmlspecialchars($mission['advice'] ?? '—'); ?></p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 anim-fade-in-up">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Objectif principal</h5>
                    <p class="text-secondary small mb-0">L'objectif que vous devez prioriser.</p>
                </div>
                <i class="bi bi-star-fill text-warning fs-4"></i>
            </div>
            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($savingPlan['primaryObjective']['name'] ?? 'Aucun objectif'); ?></h4>
            <p class="text-muted small mb-2">Échéance : <?php echo htmlspecialchars($savingPlan['primaryObjective']['deadline'] ?? '—'); ?></p>
            <p class="mb-0 text-dark small">Il reste <?php echo htmlspecialchars($savingPlan['primaryObjective']['remaining'] ?? '0'); ?> DT à épargner.</p>
            <div class="progress mt-4" style="height: 8px; background-color: rgba(15, 23, 42, 0.08);">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min(100, $savingPlan['primaryObjective']['progress'] ?? 0); ?>%"></div>
            </div>
            <div class="mt-2 text-secondary small">Progression : <?php echo $savingPlan['primaryObjective']['progress'] ?? 0; ?>%</div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Santé financière</h5>
                    <p class="text-secondary small mb-0">Scores clés pour savoir où améliorer.</p>
                </div>
                <span class="badge bg-primary-light text-primary">Global <?php echo $financialHealth['overallScore'] ?? 0; ?>/100</span>
            </div>
            <?php $scores = [
                ['label' => 'Cashflow', 'score' => $financialHealth['cashflowScore'] ?? 0],
                ['label' => 'Budget', 'score' => $financialHealth['budgetScore'] ?? 0],
                ['label' => 'Épargne', 'score' => $financialHealth['savingsScore'] ?? 0],
                ['label' => 'Objectifs', 'score' => $financialHealth['goalsScore'] ?? 0],
                ['label' => 'Habitudes', 'score' => $financialHealth['habitsScore'] ?? 0],
            ]; ?>
            <div class="row g-3">
                <?php foreach ($scores as $score): ?>
                <div class="col-12">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span><?php echo htmlspecialchars($score['label']); ?></span>
                        <strong><?php echo $score['score']; ?>%</strong>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--border);">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $score['score']; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Mon Financial DNA</h5>
                    <p class="text-secondary small mb-0">Votre comportement financier analysé.</p>
                </div>
                <span class="badge bg-warning-light text-warning"><?php echo htmlspecialchars($financialDna['label'] ?? 'Profil'); ?></span>
            </div>
            <p class="mb-3 text-dark"><?php echo htmlspecialchars($financialDna['description'] ?? 'Analyse basée sur votre comportement récent.'); ?></p>
            <?php if (!empty($financialDna['traits'])): ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($financialDna['traits'] as $trait): ?>
                <span class="badge bg-secondary-light text-secondary"><?php echo htmlspecialchars($trait); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 anim-fade-in-up">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Prévisions</h5>
                    <p class="text-secondary small mb-0">Ce que votre copilote anticipe pour votre budget.</p>
                </div>
                <span class="badge bg-info-light text-info">Projection</span>
            </div>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($predictions as $prediction): ?>
                <div class="alert alert-modern bg-light border-0 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-<?php echo $prediction['type'] === 'warning' ? 'danger' : ($prediction['type'] === 'success' ? 'success' : 'info'); ?>-light text-<?php echo $prediction['type'] === 'warning' ? 'danger' : ($prediction['type'] === 'success' ? 'success' : 'info'); ?> rounded-circle p-2">
                            <i class="bi bi-<?php echo $prediction['type'] === 'warning' ? 'exclamation-triangle' : ($prediction['type'] === 'success' ? 'check-circle' : 'info-circle'); ?>"></i>
                        </span>
                        <div>
                            <p class="mb-0 text-dark small"><?php echo htmlspecialchars($prediction['message']); ?></p>
                            <small class="text-muted"><?php echo htmlspecialchars($prediction['time'] ?? 'Aujourd\'hui'); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Notifications intelligentes</h5>
                    <p class="text-secondary small mb-0">Seulement les alertes qui ont un vrai impact.</p>
                </div>
                <span class="badge bg-primary-light text-primary">Alerte</span>
            </div>
            <?php if (!empty($notifications)): ?>
            <div class="list-group">
                <?php foreach ($notifications as $note): ?>
                <div class="list-group-item border-0 py-3 px-0 rounded-4 mb-2" style="background: rgba(248,249,250,0.9);">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <p class="mb-1 text-dark small"><?php echo htmlspecialchars($note['message']); ?></p>
                            <small class="text-muted"><?php echo htmlspecialchars($note['time']); ?></small>
                        </div>
                        <span class="badge bg-<?php echo $note['type'] === 'warning' ? 'danger' : ($note['type'] === 'success' ? 'success' : 'info'); ?>-light text-<?php echo $note['type'] === 'warning' ? 'danger' : ($note['type'] === 'success' ? 'success' : 'info'); ?>"><?php echo ucfirst($note['type']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-0">Aucune notification utile pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 anim-fade-in-up">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Factures à venir</h5>
                    <p class="text-secondary small mb-0">Anticipez vos prochaines échéances.</p>
                </div>
                <span class="badge bg-secondary-light text-secondary"><?php echo count($upcomingBills); ?> items</span>
            </div>
            <?php if (!empty($upcomingBills)): ?>
            <div class="list-group">
                <?php foreach ($upcomingBills as $bill): ?>
                <div class="list-group-item border-0 py-3 px-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($bill['name']); ?></strong>
                            <div class="text-muted small"><?php echo htmlspecialchars($bill['category']); ?></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold"><?php echo number_format($bill['amount'], 2); ?> DT</div>
                            <small class="text-muted"><?php echo htmlspecialchars($bill['dueDate']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-0">Aucune facture planifiée pour l'instant.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Plan de priorisation</h5>
                    <p class="text-secondary small mb-0">Quel objectif prioriser et pourquoi.</p>
                </div>
                <i class="bi bi-lightning-charge-fill text-primary fs-4"></i>
            </div>
            <p class="text-dark mb-2"><?php echo htmlspecialchars($goalStrategy['recommendation'] ?? 'Créez des objectifs pour recevoir une stratégie.'); ?></p>
            <?php if (!empty($goalStrategy['details'])): ?>
            <div class="row g-3">
                <?php foreach ($goalStrategy['details'] as $key => $detail): ?>
                <div class="col-12">
                    <div class="p-3 bg-light rounded-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><?php echo ucfirst($key); ?></strong>
                            <span class="badge bg-primary-light text-primary"><?php echo htmlspecialchars($detail['deadline'] ?? '—'); ?></span>
                        </div>
                        <div class="text-muted small">Restant : <?php echo htmlspecialchars($detail['remaining'] ?? '0'); ?> DT</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="module" src="assets/js/dashboard.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
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
