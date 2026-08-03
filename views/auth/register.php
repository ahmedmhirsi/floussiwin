<?php $error = $error ?? null; include __DIR__ . '/../partials/header.php'; ?>

<div class="container py-5 anim-fade-in-up">
    <div class="row justify-content-center py-5">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg p-4 p-md-5" style="border-radius: var(--radius-lg);">
                <div class="text-center mb-4">
                    <div class="logo-icon bg-primary text-white mx-auto mb-3" style="width: 48px; height: 48px; border-radius: var(--radius-md); font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h3 class="fw-bold mb-2" style="font-family: var(--font-heading); letter-spacing: -0.02em;">Créer un compte</h3>
                    <p class="text-secondary small">Rejoignez Flousi Win et optimisez votre épargne</p>
                </div>

                <div id="toast-container"></div>
                
                <form id="register-form" novalidate>
                    <!-- Floating Name Field -->
                    <div class="form-group-premium form-floating-premium mb-1">
                        <input type="text" name="name" id="name" class="form-control-premium" required placeholder=" " autocomplete="name">
                        <label for="name"><i class="bi bi-person me-1"></i>Nom complet</label>
                    </div>
                    <small class="field-error-msg mb-3 d-block text-danger" id="error-name"></small>
                    
                    <!-- Floating Email Field -->
                    <div class="form-group-premium form-floating-premium mb-1">
                        <input type="email" name="email" id="email" class="form-control-premium" required placeholder=" " autocomplete="email">
                        <label for="email"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                    </div>
                    <small class="field-error-msg mb-3 d-block text-danger" id="error-email"></small>
                    
                    <!-- Floating Password Field -->
                    <div class="form-group-premium form-floating-premium mb-2">
                        <input type="password" name="password" id="password" class="form-control-premium" required placeholder=" ">
                        <label for="password"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-secondary">Force du mot de passe :</span>
                            <span id="password-desc" class="badge bg-secondary badge-premium">Très faible</span>
                        </div>
                        <progress id="password-strength" min="0" max="4" value="0" class="w-100" style="height: 6px; border-radius: var(--radius-full); overflow: hidden;"></progress>
                    </div>
                    <small class="field-error-msg mb-4 d-block text-danger" id="error-password"></small>
                    
                    <div class="d-flex align-items-center mt-4">
                        <button type="submit" id="submit-btn" class="btn btn-premium w-100 py-3">
                            <i class="bi bi-person-plus me-2"></i>Créer mon compte
                        </button>
                        <span id="submit-loader" class="spinner-border text-primary ms-3" style="display:none; width: 1.5rem; height: 1.5rem;" role="status"></span>
                    </div>
                </form>
                
                <p class="text-center text-secondary small mt-4 mb-0">
                    Déjà inscrit ? <a href="index.php?route=login" class="text-primary fw-semibold text-decoration-none">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script type="module" src="assets/js/register.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
