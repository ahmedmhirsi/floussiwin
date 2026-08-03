<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Goals Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 anim-fade-in-up">
    <div>
        <h2 class="page-title mb-1" style="font-family: var(--font-heading);">Objectifs d'épargne</h2>
        <p class="page-subtitle mb-0">Planifiez vos rêves, suivez vos progrès et atteignez vos sommets financiers.</p>
    </div>
    <div>
        <button id="btn-add-goal" class="btn btn-premium">
            <i class="bi bi-plus-circle me-2"></i>Nouvel objectif
        </button>
    </div>
</div>

<!-- Active Goals Row -->
<div class="row g-4 anim-fade-in-up" id="goals-container">
    <?php if (empty($goals)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center bg-card" style="border-radius: var(--radius-lg);">
                <div class="bg-primary-light text-primary p-4 rounded-circle d-inline-flex mb-3">
                    <i class="bi bi-flag-fill fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" style="font-family: var(--font-heading);">Aucun objectif actif</h5>
                <p class="text-secondary small mb-4 mx-auto" style="max-width: 320px;">
                    Vous n'avez pas encore défini d'objectif d'épargne. Créez-en un dès maintenant pour commencer à économiser !
                </p>
                <button onclick="document.getElementById('btn-add-goal').click();" class="btn btn-outline-premium btn-sm">
                    Créer mon premier objectif
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($goals as $g):
            $percent = $g['target_amount'] > 0 ? round($g['saved_amount'] / $g['target_amount'] * 100, 1) : 0;
            $remaining = $g['target_amount'] - $g['saved_amount'];
            
            // Map category icon
            $name = strtolower($g['name']);
            $icon = 'bi-bullseye';
            $bgClass = 'primary-light';
            $textClass = 'primary';
            
            if (strpos($name, 'vacance') !== false || strpos($name, 'voyage') !== false) {
                $icon = 'bi-airplane-fill';
                $bgClass = 'success-light';
                $textClass = 'success';
            } elseif (strpos($name, 'voiture') !== false || strpos($name, 'moto') !== false) {
                $icon = 'bi-car-front-fill';
                $bgClass = 'warning-light';
                $textClass = 'warning';
            } elseif (strpos($name, 'ordinateur') !== false || strpos($name, 'telephone') !== false || strpos($name, 'tech') !== false) {
                $icon = 'bi-laptop';
                $bgClass = 'primary-light';
                $textClass = 'primary';
            } elseif (strpos($name, 'maison') !== false || strpos($name, 'immobilier') !== false) {
                $icon = 'bi-house-fill';
                $bgClass = 'danger-light';
                $textClass = 'danger';
            }
        ?>
        <div class="col-md-6 col-lg-4 stagger-item">
            <div class="goal-card">
                <div class="goal-card-header">
                    <div class="goal-category-icon bg-<?php echo $bgClass; ?> text-<?php echo $textClass; ?>">
                        <i class="bi <?php echo $icon; ?>"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="goal-card-title"><?php echo htmlspecialchars($g['name']); ?></h5>
                        <span class="badge bg-<?php echo $percent >= 100 ? 'success' : 'primary'; ?>-light text-<?php echo $percent >= 100 ? 'success' : 'primary'; ?> badge-premium">
                            <?php echo $percent >= 100 ? 'Terminé' : 'En cours'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="goal-card-body">
                    <div class="goal-progress-info">
                        <span>Épargné : <?php echo number_format($g['saved_amount'], 2); ?> DT</span>
                        <span>Cible : <?php echo number_format($g['target_amount'], 2); ?> DT</span>
                    </div>
                    
                    <div class="goal-progress-bar-wrapper">
                        <div class="goal-progress-bar bg-<?php echo $percent >= 100 ? 'success' : 'primary'; ?>" style="width: <?php echo min($percent, 100); ?>%"></div>
                    </div>
                    
                    <div class="goal-meta-row">
                        <?php if ($remaining > 0): ?>
                            <span>Reste : <strong><?php echo number_format($remaining, 2); ?> DT</strong></span>
                        <?php else: ?>
                            <span class="text-success fw-semibold"><i class="bi bi-patch-check-fill me-1"></i>Objectif atteint ! 🎉</span>
                        <?php endif; ?>
                        
                        <span><?php echo $percent; ?>%</span>
                    </div>
                    
                    <?php if (!empty($g['deadline'])): ?>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center gap-1.5 text-muted small">
                            <i class="bi bi-calendar3"></i>
                            <span>Date limite : <?php echo date('d/m/Y', strtotime($g['deadline'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="goal-card-actions">
                    <?php if ($remaining > 0): ?>
                    <button class="btn btn-premium btn-sm flex-grow-1 py-2 btn-add-savings" data-id="<?php echo $g['id']; ?>" data-saved="<?php echo $g['saved_amount']; ?>">
                        <i class="bi bi-plus-circle me-1"></i>Contribuer
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-icon btn-sm btn-edit-goal" data-id="<?php echo $g['id']; ?>" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-icon btn-sm text-danger btn-delete-goal" data-id="<?php echo $g['id']; ?>" title="Supprimer">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Create/Edit Goal -->
<div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="goalModalTitle">Nouvel objectif</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="goal-form" class="p-1">
            <input type="hidden" name="id" id="goal-id">
            
            <div class="form-group-premium form-floating-premium mb-3">
                <input id="goal-name" name="name" type="text" class="form-control-premium" required placeholder=" ">
                <label for="goal-name"><i class="bi bi-bookmark-fill me-1"></i>Nom de l'objectif</label>
            </div>
            
            <div class="form-group-premium form-floating-premium mb-3">
                <input id="goal-target" name="target_amount" type="number" step="0.01" class="form-control-premium" required placeholder=" ">
                <label for="goal-target"><i class="bi bi-cash me-1"></i>Montant cible (DT)</label>
            </div>
            
            <div class="form-group-premium form-floating-premium mb-3">
                <input id="goal-saved" name="saved_amount" type="number" step="0.01" class="form-control-premium" value="0" placeholder=" ">
                <label for="goal-saved"><i class="bi bi-piggy-bank me-1"></i>Montant déjà épargné (DT)</label>
            </div>
            
            <div class="form-group-premium form-floating-premium mb-2">
                <input id="goal-deadline" name="deadline" type="date" class="form-control-premium" placeholder=" ">
                <label for="goal-deadline"><i class="bi bi-calendar-event me-1"></i>Date limite (optionnel)</label>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-premium" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="goal-save" class="btn btn-premium">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Savings -->
<div class="modal fade" id="savingsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Ajouter de l'épargne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="savings-form" class="p-1">
            <input type="hidden" name="goal_id" id="savings-goal-id">
            
            <div class="form-group-premium form-floating-premium mb-2">
                <input id="savings-amount" name="amount" type="number" step="0.01" class="form-control-premium" required placeholder=" " autofocus>
                <label for="savings-amount"><i class="bi bi-plus-lg me-1"></i>Montant (DT)</label>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-premium btn-sm" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="savings-save" class="btn btn-premium btn-sm">Ajouter</button>
      </div>
    </div>
  </div>
</div>

<script type="module" src="assets/js/goals.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
