<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Configuration du Profil</h2>
        <small class="text-muted">Configurez vos revenus et charges fixes pour des recommandations personnalisées</small>
    </div>
</div>

<div class="row g-4">
    <!-- Revenus -->
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h5 class="mb-3"><i class="bi bi-wallet2 me-2"></i>Vos Revenus</h5>
            <form id="profile-form">
                <div class="mb-3">
                    <label>Salaire mensuel (DT)</label>
                    <input type="number" id="monthly-salary" name="monthly_salary" class="form-control input-modern" 
                           value="<?php echo $profile ? number_format($profile['monthly_salary'], 2, '.', '') : ''; ?>" step="0.01">
                    <small class="text-muted">Votre salaire principal mensuel</small>
                </div>
                <div class="mb-3">
                    <label>Revenus supplémentaires (DT)</label>
                    <input type="number" id="additional-income" name="additional_income" class="form-control input-modern"
                           value="<?php echo $profile ? number_format($profile['additional_income'], 2, '.', '') : ''; ?>" step="0.01">
                    <small class="text-muted">Freelance, primes, etc.</small>
                </div>
                <div class="mb-3">
                    <label>Total mensuel estimé</label>
                    <div class="form-control input-modern bg-light" id="total-income-display">
                        <?php echo $profile ? number_format($profile['total_monthly_income'], 2) : '0.00'; ?> DT
                    </div>
                </div>
                <button type="button" id="save-profile" class="btn btn-premium w-100">
                    <i class="bi bi-check-circle me-2"></i>Enregistrer les revenus
                </button>
            </form>
        </div>
    </div>

    <!-- Charges Fixes -->
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Charges Fixes</h5>
                <button type="button" id="add-fixed-expense" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i>Ajouter
                </button>
            </div>
            
            <div id="fixed-expenses-list">
                <?php if (empty($fixedExpenses)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 mb-2"></i>
                        <p>Aucune charge fixe configurée</p>
                        <small>Ajoutez vos dépenses récurrentes (loyer, transport, etc.)</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($fixedExpenses as $expense): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong><?php echo htmlspecialchars($expense['name']); ?></strong>
                                <?php if ($expense['category']): ?>
                                    <small class="text-muted">(<?php echo htmlspecialchars($expense['category']); ?>)</small>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold"><?php echo number_format($expense['amount'], 2); ?> DT</span>
                                <button class="btn btn-sm btn-outline-secondary btn-edit-expense" data-id="<?php echo $expense['id']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-expense" data-id="<?php echo $expense['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between">
                    <strong>Total charges fixes:</strong>
                    <strong id="total-fixed-expenses">
                        <?php 
                        $totalFixed = 0;
                        foreach ($fixedExpenses as $e) $totalFixed += $e['amount'];
                        echo number_format($totalFixed, 2);
                        ?> DT
                    </strong>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted">Disponible après charges:</span>
                    <span class="text-success fw-bold" id="available-after-fixed">
                        <?php 
                        $totalIncome = $profile ? $profile['total_monthly_income'] : 0;
                        $available = $totalIncome - $totalFixed;
                        echo number_format(max(0, $available), 2);
                        ?> DT
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ajouter/Modifier charge fixe -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="expenseModalTitle">Nouvelle charge fixe</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="expense-form">
            <input type="hidden" name="id" id="expense-id">
            <div class="mb-3">
                <label>Nom de la charge</label>
                <input id="expense-name" name="name" type="text" class="form-control" required
                       placeholder="Ex: Loyer, Transport, Internet...">
            </div>
            <div class="mb-3">
                <label>Montant mensuel (DT)</label>
                <input id="expense-amount" name="amount" type="number" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Catégorie (optionnel)</label>
                <select id="expense-category" name="category" class="form-control">
                    <option value="">Sélectionner</option>
                    <option value="Logement">Logement</option>
                    <option value="Transport">Transport</option>
                    <option value="Alimentation">Alimentation</option>
                    <option value="Factures">Factures (STEG/SONEDE)</option>
                    <option value="Internet/Téléphone">Internet/Téléphone</option>
                    <option value="Loisirs">Loisirs</option>
                    <option value="Santé">Santé</option>
                    <option value="Autres">Autres</option>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="save-expense" class="btn btn-premium">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card-modern p-4 bg-info bg-opacity-10">
            <h5 class="mb-3"><i class="bi bi-lightbulb me-2"></i>Conseil</h5>
            <p class="mb-0">
                Une fois votre profil configuré, l'application calculera automatiquement vos recommandations d'épargne quotidiennes 
                en fonction de vos revenus, charges fixes et objectifs. Le système s'adapte en temps réel à vos dépenses réelles.
            </p>
        </div>
    </div>
</div>

<script type="module" src="assets/js/profile.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
