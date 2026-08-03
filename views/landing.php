<?php include __DIR__ . '/partials/header.php'; ?>

<!-- Hero Section -->
<div class="container py-5 anim-fade-in-up">
    <div class="row align-items-center g-5 py-5">
        <div class="col-lg-6">
            <span class="badge bg-primary-light text-primary badge-premium mb-3 px-3 py-2 anim-pulse-glow">🌟 Version 2.0 Premium</span>
            <h1 class="display-4 fw-bold lh-sm mb-4" style="font-family: var(--font-heading); font-weight: 700; letter-spacing: -0.03em;">
                Gérez vos finances avec la simplicité de la <span class="text-primary bg-primary-light px-2 rounded-3">FinTech</span>
            </h1>
            <p class="lead text-secondary mb-4" style="font-size: 1.15rem; line-height: 1.6;">
                Flousi Win vous aide à analyser vos dépenses, à planifier vos objectifs d'épargne et à économiser intelligemment au quotidien. Conçu spécialement pour la Tunisie.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="index.php?route=register" class="btn btn-premium px-4 py-3">
                    <i class="bi bi-rocket-takeoff me-2"></i>Commencer gratuitement
                </a>
                <a href="index.php?route=login" class="btn btn-outline-premium px-4 py-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </a>
            </div>
            <div class="mt-5 d-flex align-items-center gap-4 text-muted small">
                <div><i class="bi bi-shield-check text-success me-1 fs-5"></i> Sécurisé à 100%</div>
                <div><i class="bi bi-check-circle text-success me-1 fs-5"></i> Sans engagement</div>
            </div>
        </div>
        
        <div class="col-lg-6 text-center text-lg-end anim-float">
            <!-- Visual mock card -->
            <div class="glass p-4 rounded-4 shadow-xl border border-white border-opacity-20 d-inline-block text-start position-relative" style="max-width: 440px; background: rgba(255,255,255,0.75);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white p-2 rounded-3 d-flex"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <span class="text-muted small d-block">Solde Restant</span>
                            <span class="fw-bold fs-5 text-dark">2,450.00 DT</span>
                        </div>
                    </div>
                    <span class="badge bg-success-light text-success badge-premium">+12.4%</span>
                </div>
                
                <div class="mb-4">
                    <span class="text-muted small d-block mb-1">Objectif : Nouvel Ordinateur</span>
                    <div class="progress" style="height: 6px; background-color: var(--border);">
                        <div class="progress-bar bg-primary" style="width: 75%;"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span>1,500 DT / 2,000 DT</span>
                        <span>75%</span>
                    </div>
                </div>
                
                <div class="list-group-premium">
                    <div class="list-group-item-premium p-2 border-0 bg-transparent d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-danger-light text-danger p-2 rounded-circle d-flex" style="font-size: 0.8rem;"><i class="bi bi-cart3"></i></div>
                            <div>
                                <strong class="text-dark small d-block">Courses Monoprix</strong>
                                <small class="text-muted">Aujourd'hui</small>
                            </div>
                        </div>
                        <span class="text-danger fw-bold small">-45.50 DT</span>
                    </div>
                    
                    <div class="list-group-item-premium p-2 border-0 bg-transparent d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success-light text-success p-2 rounded-circle d-flex" style="font-size: 0.8rem;"><i class="bi bi-cash"></i></div>
                            <div>
                                <strong class="text-dark small d-block">Salaire Mensuel</strong>
                                <small class="text-muted">Hier</small>
                            </div>
                        </div>
                        <span class="text-success fw-bold small">+1,800.00 DT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-light py-5 border-top border-bottom">
    <div class="container py-5">
        <div class="text-center mb-5 anim-fade-in-up">
            <h2 class="fw-bold mb-3" style="font-family: var(--font-heading);">Une suite d'outils financiers puissants</h2>
            <p class="text-secondary" style="max-width: 600px; margin: 0 auto;">Tout ce dont vous avez besoin pour prendre le contrôle total de votre budget et planifier votre avenir financier.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100 anim-fade-in-up" style="border-radius: var(--radius-lg);">
                    <div class="bg-primary-light text-primary p-3 rounded-4 d-inline-flex mb-4"><i class="bi bi-speedometer2 fs-3"></i></div>
                    <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);">Tableau de Bord en temps réel</h5>
                    <p class="text-secondary small mb-0">Suivez vos revenus, vos dépenses réelles et votre reste à vivre quotidien d'un seul coup d'œil.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100 anim-fade-in-up" style="border-radius: var(--radius-lg);">
                    <div class="bg-success-light text-success p-3 rounded-4 d-inline-flex mb-4"><i class="bi bi-piggy-bank fs-3"></i></div>
                    <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);">Smart Saving Board</h5>
                    <p class="text-secondary small mb-0">Un tableau d'épargne intelligent configuré selon vos objectifs réels de vie pour économiser à votre rythme.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100 anim-fade-in-up" style="border-radius: var(--radius-lg);">
                    <div class="bg-warning-light text-warning p-3 rounded-4 d-inline-flex mb-4"><i class="bi bi-lightbulb fs-3"></i></div>
                    <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);">Recommandations IA</h5>
                    <p class="text-secondary small mb-0">Notre moteur d'analyse calcule et vous propose des objectifs d'épargne quotidiens adaptés à vos dépenses.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
