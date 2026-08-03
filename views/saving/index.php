<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="page-header page-header-savings anim-fade-in-up">
    <div>
        <span class="badge bg-primary-light text-primary badge-premium mb-3">Plan d'épargne intelligent</span>
        <h1 class="page-title mb-2">Smart Saving Plan 🎯</h1>
        <p class="page-subtitle">Un plan d'épargne personnalisé chaque jour, adapté à vos objectifs et à votre budget.</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="index.php?route=dashboard" class="btn btn-outline-premium btn-sm">Retour à Mon Coach</a>
        <a href="index.php?route=goals" class="btn btn-premium btn-sm">Voir mes objectifs</a>
    </div>
</div>

<div class="row g-4 mb-4 anim-fade-in-up">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card savings">
            <span class="stat-card-label">Total épargné</span>
            <div id="stat-saved" class="stat-card-value text-success"><?php echo $boardData['stats']['saved']; ?> DT</div>
            <div class="stat-card-footer">Sur <span id="stat-total-goals"><?php echo $boardData['stats']['totalGoals']; ?></span> objectifs</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card balance">
            <span class="stat-card-label">Objectif total</span>
            <div id="stat-goal" class="stat-card-value text-primary"><?php echo $boardData['stats']['goal']; ?> DT</div>
            <div class="stat-card-footer">Montant à atteindre</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card expenses">
            <span class="stat-card-label">Restant</span>
            <div id="stat-remaining" class="stat-card-value text-danger"><?php echo $boardData['stats']['remaining']; ?> DT</div>
            <div class="stat-card-footer">Reste à économiser</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card income">
            <span class="stat-card-label">Jours restants</span>
            <div id="stat-days-remaining" class="stat-card-value text-warning"><?php echo $boardData['stats']['daysRemaining']; ?></div>
            <div class="stat-card-footer">Ce mois-ci</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 anim-fade-in-up">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-lg);">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Tableau d'épargne du mois</h5>
                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($boardData['monthLabel']); ?></p>
                </div>
                <span id="saving-progress-badge" class="badge bg-primary-light text-primary badge-premium">Progression <?php echo $boardData['progressPercent']; ?>%</span>
            </div>

            <div class="saving-board-grid">
                <?php foreach ($boardData['cells'] as $cell): ?>
                    <?php $classes = ['saving-cell'];
                          if ($cell['completed']) $classes[] = 'completed';
                          if ($cell['today']) $classes[] = 'today';
                          if ($cell['weekend']) $classes[] = 'weekend';
                    ?>
                    <div class="<?php echo implode(' ', $classes); ?>" data-day="<?php echo $cell['date']; ?>" data-amount="<?php echo $cell['amount']; ?>">
                        <span class="saving-cell-day"><?php echo $cell['dayLabel']; ?></span>
                        <span class="saving-cell-amount"><?php echo $cell['amount']; ?> DT</span>
                        <?php if ($cell['completed']): ?>
                            <span class="saving-cell-tag">Terminé</span>
                        <?php elseif ($cell['today']): ?>
                            <span class="saving-cell-tag today-tag">Aujourd'hui</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="w-100 w-md-50">
                        <div class="progress" style="height: 10px; background: var(--bg-secondary);">
                            <div id="saving-board-progress" class="progress-bar bg-success" role="progressbar" style="width: <?php echo $boardData['progressPercent']; ?>%;" aria-valuenow="<?php echo $boardData['progressPercent']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $boardData['progressPercent']; ?>%</div>
                        </div>
                    </div>
                    <small class="text-muted">Cliquez sur une cellule pour marquer le jour comme complété.</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius-lg); background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(59, 130, 246, 0.08));">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="bg-white rounded-4 p-3 shadow-sm text-primary"><i class="bi bi-lightbulb-fill fs-4"></i></div>
                <div>
                    <h5 class="fw-bold mb-1">Recommandation du jour</h5>
                    <p class="text-muted small mb-0">Montant recommandé pour conserver le rythme d'épargne.</p>
                </div>
            </div>
            <div class="mb-4">
                <div class="display-5 fw-bold text-primary"><?php echo $boardData['recommendation']['recommended_savings']; ?> DT</div>
                <span class="badge bg-success-light text-success badge-premium">Budget quotidien</span>
            </div>
            <div class="rounded-4 p-3" style="background: rgba(255,255,255,0.7);">
                <p class="mb-0 text-muted small"><?php echo htmlspecialchars($boardData['recommendation']['reason']); ?></p>
            </div>
            <div class="mt-4 pt-3 border-top border-white border-opacity-30">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Dépenses d'hier</span>
                    <strong class="text-dark small"><?php echo number_format($boardData['recommendation']['yesterday_expenses'] ?? 0, 2); ?> DT</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary small">Budget journalier</span>
                    <strong class="text-dark small"><?php echo number_format($boardData['recommendation']['daily_budget'] ?? 0, 2); ?> DT</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="assets/js/saving.js"></script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
