<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/DailyRecommendation.php';
require_once __DIR__ . '/../models/FinancialInsight.php';
require_once __DIR__ . '/../services/BudgetService.php';
require_once __DIR__ . '/../services/RecommendationService.php';
require_once __DIR__ . '/../services/SavingPlanService.php';
require_once __DIR__ . '/../services/FinancialScoreService.php';
require_once __DIR__ . '/../services/ForecastService.php';
require_once __DIR__ . '/../services/FinancialDnaService.php';
require_once __DIR__ . '/../services/GoalPlannerService.php';
require_once __DIR__ . '/../services/NotificationService.php';

class DashboardController
{
    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userModel = new User($GLOBALS['pdo']);
        $user = $userModel->findById($userId);
        $userName = $user ? $user['name'] : '';

        $transactionModel = new Transaction($GLOBALS['pdo']);
        $goalModel = new Goal($GLOBALS['pdo']);
        $userProfile = new UserProfile($GLOBALS['pdo']);
        $profile = $userProfile->findByUserId($userId);

        $budgetService = new BudgetService($GLOBALS['pdo']);
        $recommendationService = new RecommendationService($GLOBALS['pdo']);
        $savingPlanService = new SavingPlanService($GLOBALS['pdo']);
        $financialScoreService = new FinancialScoreService($GLOBALS['pdo']);
        $forecastService = new ForecastService($GLOBALS['pdo']);
        $financialDnaService = new FinancialDnaService($GLOBALS['pdo']);
        $goalPlannerService = new GoalPlannerService($GLOBALS['pdo']);
        $notificationService = new NotificationService($GLOBALS['pdo']);

        $dailyData = $recommendationService->generateDailyInsights($userId);
        $dailyRecommendation = $dailyData['recommendation'];
        $insights = $dailyData['insights'];
        $mission = $recommendationService->generateDailyMission($userId);

        $budgetSummary = $budgetService->getAvailableToday($userId);
        $upcomingBills = $budgetService->getUpcomingBills($userId, 3);
        $savingPlan = $savingPlanService->buildSmartSavingPlan($userId);
        $financialHealth = $financialScoreService->calculateScores($userId);
        $predictions = $forecastService->getPredictions($userId);
        $financialDna = $financialDnaService->analyze($userId);
        $goalStrategy = $goalPlannerService->getGoalStrategy($userId);
        $notifications = $notificationService->getSmartNotifications($userId);

        $goals = $goalModel->getActiveGoalsByUser($userId);
        $initialTransactions = $transactionModel->getByUser($userId, null, null, null, null, 5);

        include __DIR__ . '/../views/dashboard/index.php';
    }

    public function savingChallenge()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userModel = new User($GLOBALS['pdo']);
        $user = $userModel->findById($userId);
        $userName = $user ? $user['name'] : '';

        $savingPlanService = new SavingPlanService($GLOBALS['pdo']);
        $boardData = $savingPlanService->buildSmartSavingPlan($userId);

        include __DIR__ . '/../views/saving/index.php';
    }
}
