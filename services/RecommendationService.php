<?php
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/FixedExpense.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/DailyRecommendation.php';
require_once __DIR__ . '/../models/FinancialInsight.php';

class RecommendationService
{
    private $pdo;
    private $userProfile;
    private $fixedExpense;
    private $transaction;
    private $goal;
    private $dailyRecommendation;
    private $financialInsight;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userProfile = new UserProfile($pdo);
        $this->fixedExpense = new FixedExpense($pdo);
        $this->transaction = new Transaction($pdo);
        $this->goal = new Goal($pdo);
        $this->dailyRecommendation = new DailyRecommendation($pdo);
        $this->financialInsight = new FinancialInsight($pdo);
    }

    public function calculateDailyRecommendation($userId, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $profile = $this->userProfile->findByUserId($userId);
        if (!$profile) {
            return [
                'recommended_savings' => 0,
                'reason' => 'Configurez votre profil financier pour recevoir un plan personnalisé.',
                'is_configured' => false,
            ];
        }

        // Ensure we can read a monthly income value even if the DB schema
        // doesn't expose the generated column `total_monthly_income`.
        if (isset($profile['total_monthly_income'])) {
            $monthlyIncome = (float) $profile['total_monthly_income'];
        } else {
            $monthlyIncome = (float) ($profile['monthly_salary'] ?? 0) + (float) ($profile['additional_income'] ?? 0);
        }

        $fixedExpenses = $this->fixedExpense->getTotalFixedExpenses($userId);
        $availableAfterFixed = max(0, $monthlyIncome - $fixedExpenses);
        $monthlyExpenses = $this->transaction->getMonthlySumByType($userId, 'expense');
        $dailyExpenses = $this->transaction->getMonthlySumByType($userId, 'expense');
        $today = date('j');
        $daysInMonth = (int) date('t');
        $averageDailyExpense = $today > 0 ? $monthlyExpenses / $today : 0;

        $goals = $this->goal->getActiveGoalsByUser($userId);
        $totalGoalRemaining = 0;
        $nearestDeadline = null;
        $daysUntilDeadline = 30;

        foreach ($goals as $goal) {
            $remaining = max(0, $goal['target_amount'] - $goal['saved_amount']);
            $totalGoalRemaining += $remaining;
            if ($goal['deadline']) {
                $deadlineDays = $this->daysBetween($date, $goal['deadline']);
                if ($deadlineDays > 0 && ($nearestDeadline === null || $deadlineDays < $daysUntilDeadline)) {
                    $nearestDeadline = $goal['deadline'];
                    $daysUntilDeadline = $deadlineDays;
                }
            }
        }

        if ($nearestDeadline === null) {
            $daysUntilDeadline = 30;
        }

        $dailyBudget = $availableAfterFixed / max(1, $daysInMonth);
        $goalSavings = $totalGoalRemaining / max(1, $daysUntilDeadline);
        $adjustment = max(-5, min(5, ($dailyBudget - $averageDailyExpense) * 0.4));

        $recommendedSavings = round(max(0, min($dailyBudget, $goalSavings + $adjustment)), 2);
        $reason = $this->generateRecommendationReason($recommendedSavings, $averageDailyExpense, $dailyBudget, $totalGoalRemaining);

        return [
            'recommended_savings' => $recommendedSavings,
            'reason' => $reason,
            'is_configured' => true,
            'total_goal_remaining' => $totalGoalRemaining,
            'days_until_deadline' => $daysUntilDeadline,
            'daily_budget' => round($dailyBudget, 2),
            'average_daily_expense' => round($averageDailyExpense, 2),
        ];
    }

    public function generateDailyInsights($userId)
    {
        $today = date('Y-m-d');
        $recommendation = $this->calculateDailyRecommendation($userId, $today);

        if ($recommendation['is_configured']) {
            $this->dailyRecommendation->create(
                $userId,
                $today,
                $recommendation['recommended_savings'],
                $recommendation['reason']
            );
        }

        $this->financialInsight->clearUserInsights($userId);
        $insights = $this->buildInsights($userId, $recommendation);
        foreach ($insights as $insight) {
            $this->financialInsight->create(
                $userId,
                $insight['type'],
                $insight['title'],
                $insight['message'],
                $insight['priority']
            );
        }

        return [
            'recommendation' => $recommendation,
            'insights' => $insights,
        ];
    }

    public function generateDailyMission($userId)
    {
        $recommendation = $this->calculateDailyRecommendation($userId);

        if (!$recommendation['is_configured']) {
            return [
                'title' => 'Configurez votre profil',
                'description' => 'Complétez vos revenus et charges pour recevoir une mission personnalisée.',
                'advice' => 'Votre copilote a besoin de vos données pour vous guider.',
            ];
        }

        if ($recommendation['recommended_savings'] <= 0) {
            return [
                'title' => 'Mission de stabilité',
                'description' => 'Restez dans votre budget aujourd\'hui et évitez les dépenses non essentielles.',
                'advice' => 'Ceci aide à préserver votre trésorerie et éviter un stress financier.',
            ];
        }

        return [
            'title' => 'Mission du jour',
            'description' => 'Épargnez ' . number_format($recommendation['recommended_savings'], 2) . ' DT aujourd\'hui.',
            'advice' => 'Cela aligne votre budget sur vos objectifs prioritaires.',
        ];
    }

    public function getTopExpenseCategory($userId)
    {
        $categories = $this->transaction->getCategorySumsThisMonth($userId);
        if (empty($categories)) {
            return null;
        }
        usort($categories, fn($a, $b) => $b['total'] <=> $a['total']);
        return $categories[0];
    }

    private function buildInsights($userId, $recommendation)
    {
        $insights = [];
        $summary = $this->transaction->getMonthlySumByType($userId, 'expense');
        $profile = $this->userProfile->findByUserId($userId);
        if (!$profile) {
            return [];
        }

        $goals = $this->goal->getActiveGoalsByUser($userId);
        $fixedExpenses = $this->fixedExpense->getTotalFixedExpenses($userId);
        $netAfterFixed = max(0, $profile['total_monthly_income'] - $fixedExpenses);

        if ($netAfterFixed <= 0) {
            $insights[] = [
                'type' => 'critical',
                'title' => 'Attention trésorerie',
                'message' => 'Vos dépenses fixes dépassent ou égalent vos revenus. Réduisez vos charges ou augmentez vos revenus.',
                'priority' => 3
            ];
        }

        if ($recommendation['recommended_savings'] <= 0) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Aucune épargne possible aujourd\'hui',
                'message' => 'Vous êtes déjà au maximum de votre cashflow disponible. Évitez les achats non essentiels.',
                'priority' => 2
            ];
        }

        foreach ($goals as $goal) {
            $remaining = max(0, $goal['target_amount'] - $goal['saved_amount']);
            if ($remaining <= 0) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Objectif atteint',
                    'message' => "Vous avez complété l'objectif \"{$goal['name']}\".",
                    'priority' => 1
                ];
                continue;
            }

            if ($goal['deadline']) {
                $days = $this->daysBetween(date('Y-m-d'), $goal['deadline']);
                $required = round($remaining / max(1, $days), 2);
                if ($days <= 7) {
                    $insights[] = [
                        'type' => 'warning',
                        'title' => 'Objectif proche',
                        'message' => "{$goal['name']} doit être financé rapidement : environ {$required} DT/jour pour le respecter.",
                        'priority' => 2
                    ];
                }
            }
        }

        return $insights;
    }

    private function generateRecommendationReason($recommendedSavings, $averageDailyExpense, $dailyBudget, $goalRemaining)
    {
        if ($recommendedSavings <= 0) {
            return 'Votre budget est serré aujourd\'hui, concentrez-vous sur les dépenses essentielles.';
        }

        if ($averageDailyExpense > $dailyBudget) {
            return 'Vous dépensez plus que prévu : réduisez les catégories non prioritaires et placez une partie en épargne.';
        }

        return 'Vous pouvez épargner ce montant sans compromettre vos besoins essentiels.';
    }

    private function daysBetween($date1, $date2)
    {
        $d1 = new DateTime($date1);
        $d2 = new DateTime($date2);
        $diff = $d1->diff($d2);
        return $diff->invert ? -$diff->days : $diff->days;
    }
}
