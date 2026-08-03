<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flousi Win — Gestion Financière Intelligente</title>

    <!-- Google Font: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <!-- AOS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />

    <!-- Toastify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <!-- Flatpickr -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- App CSS layers -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/cards.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">

    <meta name="csrf-token" content="<?php echo htmlspecialchars(\Csrf::getToken() ?? ''); ?>">
</head>
<?php 
$isAuth = !empty($_SESSION['user_id']); 
$currentRoute = $_GET['route'] ?? 'home';
?>
<body class="app-body <?php echo !$isAuth ? 'no-sidebar' : ''; ?>">
<div class="app-layout">
    
    <!-- Sidebar (Rendered only if logged in) -->
    <?php if ($isAuth): ?>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="index.php?route=dashboard" class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <span class="logo-text">Flousi Win</span>
            </a>
            <button id="sidebar-toggle" class="btn btn-icon d-md-none"><i class="bi bi-x-lg"></i></button>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section-label">Navigation</div>
            <a href="index.php?route=dashboard" class="sidebar-link <?php echo $currentRoute === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Mon Coach</span>
            </a>
            <a href="index.php?route=transactions" class="sidebar-link <?php echo $currentRoute === 'transactions' ? 'active' : ''; ?>">
                <i class="bi bi-arrow-left-right"></i>
                <span>Transactions</span>
            </a>
            <a href="index.php?route=goals" class="sidebar-link <?php echo $currentRoute === 'goals' ? 'active' : ''; ?>">
                <i class="bi bi-bullseye"></i>
                <span>Objectifs</span>
            </a>
            <a href="index.php?route=saving" class="sidebar-link <?php echo in_array($currentRoute, ['saving_challenge','saving']) ? 'active' : ''; ?>">
                <i class="bi bi-piggy-bank-fill"></i>
                <span>Plan d'épargne</span>
            </a>
            
            <div class="nav-section-label">Analyses</div>
            <a href="index.php?route=reports" class="sidebar-link <?php echo $currentRoute === 'reports' ? 'active' : ''; ?>" id="nav-reports">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Rapports</span>
            </a>
            
            <div class="nav-section-label">Compte</div>
            <a href="index.php?route=profile" class="sidebar-link <?php echo $currentRoute === 'profile' ? 'active' : ''; ?>">
                <i class="bi bi-person-fill-gear"></i>
                <span>Profil</span>
            </a>
            <a href="index.php?route=profile" class="sidebar-link <?php echo $currentRoute === 'profile' ? 'active' : ''; ?>" id="nav-settings">
                <i class="bi bi-sliders"></i>
                <span>Paramètres</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="index.php?route=logout" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-left text-danger"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="main-content">
        
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-wrapper">
                <div class="d-flex align-items-center gap-3">
                    <?php if ($isAuth): ?>
                    <button id="sidebar-collapse" class="btn btn-icon d-none d-md-flex">
                        <i class="bi bi-list"></i>
                    </button>
                    <button id="sidebar-mobile-toggle" class="btn btn-icon d-md-none">
                        <i class="bi bi-list"></i>
                    </button>
                    <?php endif; ?>
                    <h1 class="topbar-title">Flousi Win</h1>
                </div>
                
                <?php if ($isAuth): ?>
                <div class="topbar-search d-none d-lg-block">
                    <i class="bi bi-search"></i>
                    <input type="text" id="topbar-search-input" class="topbar-search-input" placeholder="Rechercher une transaction...">
                </div>
                <?php endif; ?>
                
                <div class="topbar-actions">
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" class="btn-icon" title="Changer le thème">
                        <i class="bi bi-moon"></i>
                    </button>
                    
                    <?php if ($isAuth): ?>
                    <!-- Notifications Dropdown -->
                    <div class="dropdown">
                        <button class="btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="notification-bell">
                            <i class="bi bi-bell"></i>
                            <span class="badge-dot"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-premium p-0" style="width: 320px;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Notifications</h6>
                                <span class="badge bg-primary-light text-primary badge-premium">2 Nouvelles</span>
                            </div>
                            <div class="py-2" style="max-height: 240px; overflow-y: auto;">
                                <a href="#" class="dropdown-item-premium py-3 px-3 d-flex gap-3 align-items-start border-bottom">
                                    <div class="bg-success-light text-success p-2 rounded-circle">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-dark fw-semibold" style="font-size: 0.85rem;">Objectif "Vacances" atteint à 100% !</p>
                                        <small class="text-muted">Il y a 2 heures</small>
                                    </div>
                                </a>
                                <a href="#" class="dropdown-item-premium py-3 px-3 d-flex gap-3 align-items-start">
                                    <div class="bg-primary-light text-primary p-2 rounded-circle">
                                        <i class="bi bi-info-circle"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-dark fw-semibold" style="font-size: 0.85rem;">Recommandation du jour mise à jour.</p>
                                        <small class="text-muted">Il y a 5 heures</small>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2 text-center border-top bg-light">
                                <a href="#" class="text-decoration-none text-primary fw-semibold" style="font-size: 0.8rem;">Tout marquer comme lu</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Avatar & Dropdown -->
                    <div class="dropdown">
                        <div class="avatar-circle" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php 
                                $initials = 'U';
                                if (isset($_SESSION['user_name'])) {
                                    $parts = explode(' ', trim($_SESSION['user_name']));
                                    $initials = strtoupper(substr($parts[0], 0, 1));
                                    if (count($parts) > 1) {
                                        $initials .= strtoupper(substr($parts[count($parts)-1], 0, 1));
                                    }
                                }
                                echo htmlspecialchars($initials);
                            ?>
                        </div>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-premium">
                            <div class="dropdown-user-header">
                                <div class="dropdown-user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></div>
                                <div class="dropdown-user-email">Session active</div>
                            </div>
                            <a href="index.php?route=profile" class="dropdown-item-premium">
                                <i class="bi bi-person-fill"></i>
                                Mon Profil
                            </a>
                            <a href="#" class="dropdown-item-premium" id="menu-settings">
                                <i class="bi bi-sliders"></i>
                                Paramètres
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="index.php?route=logout" class="dropdown-item-premium text-danger">
                                <i class="bi bi-box-arrow-right"></i>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php if ($currentRoute !== 'login'): ?>
                            <a href="index.php?route=login" class="btn btn-premium btn-sm">Connexion</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="app-container">
