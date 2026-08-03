<?php $error = $error ?? null; include __DIR__ . '/../partials/header.php'; ?>

<div class="container py-5 anim-fade-in-up">
    <div class="row justify-content-center py-5">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg p-4 p-md-5" style="border-radius: var(--radius-lg);">
                <div class="text-center mb-4">
                    <div class="logo-icon bg-primary text-white mx-auto mb-3" style="width: 48px; height: 48px; border-radius: var(--radius-md); font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h3 class="fw-bold mb-2" style="font-family: var(--font-heading); letter-spacing: -0.02em;">Ravi de vous revoir !</h3>
                    <p class="text-secondary small">Connectez-vous pour continuer sur Flousi Win</p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-modern p-3 mb-4" style="border-radius: var(--radius-sm);">
                        <i class="bi bi-exclamation-triangle-fill text-danger alert-modern-icon"></i>
                        <div class="small text-danger fw-semibold"><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="index.php?route=login">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(\Csrf::getToken() ?? ''); ?>">
                    
                    <div class="form-group-premium form-floating-premium mb-3">
                        <input type="email" name="email" id="email" class="form-control-premium" required placeholder=" " autocomplete="email">
                        <label for="email"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                    </div>
                    
                    <div class="form-group-premium form-floating-premium mb-4">
                        <input type="password" name="password" id="password" class="form-control-premium" required placeholder=" ">
                        <label for="password"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-secondary small" for="rememberMe">Se souvenir de moi</label>
                        </div>
                        <a href="#" class="text-decoration-none text-primary small fw-semibold" id="forgot-password-link">Mot de passe oublié ?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                </form>
                
                <p class="text-center text-secondary small mt-4 mb-0">
                    Nouveau sur Flousi Win ? <a href="index.php?route=register" class="text-primary fw-semibold text-decoration-none">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
