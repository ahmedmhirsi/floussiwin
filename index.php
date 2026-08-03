<?php
// Secure session cookie params before starting session
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
];
session_set_cookie_params($cookieParams);
session_start();

// Front controller - minimal router using ?route=
require __DIR__ . '/config/database.php';
require __DIR__ . '/config/csrf.php';
// ensure CSRF token exists for views and AJAX
\Csrf::ensureToken();
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/DashboardController.php';
require __DIR__ . '/controllers/TransactionController.php';
require __DIR__ . '/controllers/GoalController.php';
require __DIR__ . '/controllers/ProfileController.php';

$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'register':
        (new AuthController())->register();
        break;
    case 'login':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'check_email':
        // AJAX endpoint to check whether an email is already registered
        (new AuthController())->checkEmail();
        break;
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'transactions':
        (new TransactionController())->index();
        break;
    case 'transactions_list':
        (new TransactionController())->listAjax();
        break;
    case 'transactions_create':
        (new TransactionController())->create();
        break;
    case 'transactions_update':
        (new TransactionController())->update();
        break;
    case 'transactions_delete':
        (new TransactionController())->delete();
        break;
    case 'transactions_summary':
        (new TransactionController())->summary();
        break;
    case 'goals':
        (new GoalController())->index();
        break;
    case 'goals_list':
        (new GoalController())->listAjax();
        break;
    case 'goals_create':
        (new GoalController())->create();
        break;
    case 'goals_update':
        (new GoalController())->update();
        break;
    case 'goals_delete':
        (new GoalController())->delete();
        break;
    case 'goals_add_savings':
        (new GoalController())->addSavings();
        break;
    case 'profile':
        (new ProfileController())->index();
        break;
    case 'profile_save':
        (new ProfileController())->saveProfile();
        break;
    case 'profile_add_expense':
        (new ProfileController())->addFixedExpense();
        break;
    case 'profile_update_expense':
        (new ProfileController())->updateFixedExpense();
        break;
    case 'profile_delete_expense':
        (new ProfileController())->deleteFixedExpense();
        break;
    case 'saving_challenge':
    case 'saving':
        (new DashboardController())->savingChallenge();
        break;
    case 'reports':
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }
        include __DIR__ . '/views/reports/index.php';
        break;
    default:
        include __DIR__ . '/views/landing.php';
        break;
}
