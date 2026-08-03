<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Transactions</h2>
    <div>
        <button id="btn-add-transaction" class="btn btn-premium">Nouvelle transaction</button>
    </div>
</div>

<div class="card-modern p-3 mb-3">
    <div class="row g-2">
        <div class="col-md-3">
            <label>Recherche</label>
            <input id="filter-q" class="form-control input-modern" placeholder="Description ou catégorie">
        </div>
        <div class="col-md-3">
            <label>Date début</label>
            <input id="filter-start" class="form-control input-modern" placeholder="YYYY-MM-DD">
        </div>
        <div class="col-md-3">
            <label>Date fin</label>
            <input id="filter-end" class="form-control input-modern" placeholder="YYYY-MM-DD">
        </div>
        <div class="col-md-3">
            <label>Catégorie</label>
            <select id="filter-category" class="form-control input-modern">
                <option value="">Toutes</option>
        <?php if (!empty($categories)): foreach ($categories as $c): ?>
                    <option value="<?php echo htmlspecialchars($c['category']); ?>"><?php echo htmlspecialchars($c['category']); ?></option>
        <?php endforeach; endif; ?>
            </select>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2">
        <button id="btn-filter" class="btn btn-outline-secondary">Appliquer</button>
        <button id="btn-reset" class="btn btn-ghost">Réinitialiser</button>
    </div>
</div>

<div id="transactions-area">
    <div class="card-modern p-3">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Catégorie</th><th>Description</th><th class="text-end">Montant (DT)</th><th></th></tr>
                </thead>
                <tbody id="transactions-table-body">
                    <?php if (!empty($initialTransactions)): foreach ($initialTransactions as $tx): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tx['date']); ?></td>
                            <td><?php echo htmlspecialchars($tx['type']); ?></td>
                            <td><?php echo htmlspecialchars($tx['category'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($tx['description'] ?? ''); ?></td>
                            <td class="text-end"><?php echo number_format($tx['amount'],2); ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1 btn-edit" data-id="<?php echo $tx['id']; ?>"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?php echo $tx['id']; ?>"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">Aucune transaction</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal (Bootstrap) -->
<div class="modal fade" id="txModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="txModalTitle">Nouvelle transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="tx-form">
            <input type="hidden" name="id" id="tx-id">
            <div class="mb-2">
                <label>Type</label>
                <select id="tx-type" name="type" class="form-control">
                    <option value="expense">Dépense</option>
                    <option value="income">Revenu</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Montant (DT)</label>
                <input id="tx-amount" name="amount" type="number" step="0.01" class="form-control">
            </div>
            <div class="mb-2">
                <label>Catégorie</label>
                <select id="tx-category" name="category" class="form-control">
                    <option value="">Sélectionner</option>
                    <?php
                    $categories = ['Nourriture / Makla','Café','Transport','Loyer','Factures STEG / SONEDE','Internet / Téléphone','Courses','Loisirs','Études','Santé','Autres'];
                    foreach ($categories as $cat) echo '<option value="'.htmlspecialchars($cat).'">'.htmlspecialchars($cat)."</option>";
                    ?>
                </select>
            </div>
            <div class="mb-2">
                <label>Date</label>
                <input id="tx-date" name="date" class="form-control">
            </div>
            <div class="mb-2">
                <label>Description</label>
                <textarea id="tx-desc" name="description" class="form-control" rows="2"></textarea>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="tx-save" class="btn btn-premium">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<script type="module" src="assets/js/transactions.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
