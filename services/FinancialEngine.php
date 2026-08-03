<?php
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/FixedExpense.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/DailyRecommendation.php';
require_once __DIR__ . '/../models/FinancialInsight.php';

class FinancialEngine
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

    /**
     * Calcule la recommandation d'épargne quotidienne pour un utilisateur
     */
    public function calculateDailyRecommendation($user_id, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        // Récupérer le profil utilisateur
        $profile = $this->userProfile->findByUserId($user_id);
        if (!$profile) {
            return [
                'recommended_savings' => 0,
                'reason' => 'Veuillez configurer votre profil financier',
                'is_configured' => false
            ];
        }

        // Récupérer les charges fixes
        $fixedExpenses = $this->fixedExpense->getTotalFixedExpenses($user_id);

        // Récupérer les objectifs actifs
        $goals = $this->goal->getActiveGoalsByUser($user_id);
        if (empty($goals)) {
            return [
                'recommended_savings' => 0,
                'reason' => 'Aucun objectif financier défini',
                'is_configured' => true
            ];
        }

        // Calculer le revenu mensuel disponible après charges fixes
        $monthlyIncome = $profile['total_monthly_income'];
        $availableAfterFixed = $monthlyIncome - $fixedExpenses;

        // Récupérer les dépenses réelles du mois en cours
        $firstDay = date('Y-m-01');
        $monthlyExpenses = $this->transaction->getMonthlySumByType($user_id, 'expense');
        $monthlyIncomeActual = $this->transaction->getMonthlySumByType($user_id, 'income');

        // Calculer les dépenses d'hier
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yesterdayExpenses = $this->getExpensesForDate($user_id, $yesterday);

        // Calculer le total restant à atteindre pour tous les objectifs
        $totalGoalRemaining = 0;
        $nearestDeadline = null;
        $daysUntilDeadline = 0;

        foreach ($goals as $g) {
            $remaining = $g['target_amount'] - $g['saved_amount'];
            $totalGoalRemaining += $remaining;

            if ($g['deadline']) {
                $deadlineDays = $this->daysBetween($date, $g['deadline']);
                if ($deadlineDays > 0 && ($nearestDeadline === null || $deadlineDays < $daysUntilDeadline)) {
                    $nearestDeadline = $g['deadline'];
                    $daysUntilDeadline = $deadlineDays;
                }
            }
        }

        // Si pas de deadline, utiliser 30 jours par défaut
        if ($daysUntilDeadline === 0) {
            $daysUntilDeadline = 30;
        }

        // Calculer le montant disponible estimé pour le reste du mois
        $daysInMonth = (int)date('t');
        $currentDay = (int)date('j');
        $daysRemaining = $daysInMonth - $currentDay + 1;

        // Estimer le budget quotidien disponible
        $dailyBudget = $availableAfterFixed / $daysInMonth;

        // Calculer les dépenses moyennes quotidiennes réelles
        $averageDailyExpense = $currentDay > 0 ? $monthlyExpenses / $currentDay : 0;

        // Calculer l'écart par rapport au budget quotidien
        $expenseVariance = $dailyBudget - $yesterdayExpenses;

        // Calculer la recommandation de base basée sur l'objectif
        $baseRecommendation = $totalGoalRemaining / $daysUntilDeadline;

        // Ajuster en fonction des dépenses d'hier
        // Si l'utilisateur a dépensé moins que prévu, recommander plus d'épargne
        // Si l'utilisateur a dépensé plus, recommander moins
        $adjustment = $expenseVariance * 0.5; // 50% de l'écart

        $recommendedSavings = max(0, $baseRecommendation + $adjustment);

        // Limiter à ne pas dépasser le budget disponible
        $maxPossible = max(0, $dailyBudget - $averageDailyExpense);
        $recommendedSavings = min($recommendedSavings, $maxPossible);

        // Arrondir à 2 décimales
        $recommendedSavings = round($recommendedSavings, 2);

        // Générer la raison
        $reason = $this->generateRecommendationReason($expenseVariance, $yesterdayExpenses, $dailyBudget);

        return [
            'recommended_savings' => $recommendedSavings,
            'reason' => $reason,
            'is_configured' => true,
            'total_goal_remaining' => $totalGoalRemaining,
            'days_until_deadline' => $daysUntilDeadline,
            'daily_budget' => $dailyBudget,
            'yesterday_expenses' => $yesterdayExpenses,
            'expense_variance' => $expenseVariance
        ];
    }

    /**
     * Génère une explication pour la recommandation
     */
    private function generateRecommendationReason($variance, $yesterdayExpenses, $dailyBudget)
    {
        if ($variance > 5) {
            return "Vous avez dépensé moins que prévu hier. Vous pouvez augmenter votre épargne aujourd'hui.";
        } elseif ($variance < -5) {
            return "Vos dépenses d'hier étaient élevées. Réduisez légèrement votre épargne aujourd'hui.";
        } else {
            return "Vos dépenses sont conformes aux prévisions. Maintenez votre rythme d'épargne.";
        }
    }

    /**
     * Analyse la faisabilité des objectifs et génère des insights
     */
    public function analyzeGoals($user_id)
    {
        $profile = $this->userProfile->findByUserId($user_id);
        if (!$profile) return [];

        $fixedExpenses = $this->fixedExpense->getTotalFixedExpenses($user_id);
        $monthlyIncome = $profile['total_monthly_income'];
        $availableAfterFixed = $monthlyIncome - $fixedExpenses;

        $goals = $this->goal->getActiveGoalsByUser($user_id);
        $insights = [];

        foreach ($goals as $g) {
            $remaining = $g['target_amount'] - $g['saved_amount'];
            
            if ($g['deadline']) {
                $daysRemaining = $this->daysBetween(date('Y-m-d'), $g['deadline']);
                
                if ($daysRemaining <= 0) {
                    $insights[] = [
                        'type' => 'critical',
                        'title' => 'Date limite dépassée',
                        'message' => "L'objectif \"{$g['name']}\" a une date limite dépassée.",
                        'priority' => 3
                    ];
                    continue;
                }

                // Calculer l'épargne quotidienne nécessaire
                $requiredDaily = $remaining / $daysRemaining;
                $maxDailySavings = $availableAfterFixed / 30; // Estimation conservatrice

                if ($requiredDaily > $maxDailySavings) {
                    $insights[] = [
                        'type' => 'warning',
                        'title' => 'Objectif difficile à atteindre',
                        'message' => "Pour atteindre \"{$g['name']}\", il faudrait économiser " . number_format($requiredDaily, 2) . " DT/jour. Avec votre budget actuel, c'est difficile.",
                        'priority' => 2
                    ];
                } elseif ($requiredDaily > $maxDailySavings * 0.8) {
                    $insights[] = [
                        'type' => 'info',
                        'title' => 'Objectif ambitieux',
                        'message' => "L'objectif \"{$g['name']}\" est réalisable mais demande de la discipline. Épargnez " . number_format($requiredDaily, 2) . " DT/jour.",
                        'priority' => 1
                    ];
                }
            }

            // Vérifier si l'objectif est déjà atteint
            if ($remaining <= 0) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Objectif atteint !',
                    'message' => "Félicitations ! Vous avez atteint votre objectif \"{$g['name']}\".",
                    'priority' => 1
                ];
            }
        }

        // Analyser les dépenses par catégorie
        $categorySums = $this->transaction->getCategorySumsThisMonth($user_id);
        foreach ($categorySums as $cat) {
            if ($cat['total'] > 200) { // Seuil arbitraire
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Dépenses élevées',
                    'message' => "Vous avez dépensé " . number_format($cat['total'], 2) . " DT en \"{$cat['category']}\" ce mois-ci.",
                    'priority' => 1
                ];
            }
        }

        return $insights;
    }

    /**
     * Sauvegarde la recommandation quotidienne et génère les insights
     */
    public function generateDailyInsights($user_id, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        // Calculer et sauvegarder la recommandation
        $recommendation = $this->calculateDailyRecommendation($user_id, $date);
        if ($recommendation['is_configured']) {
            $this->dailyRecommendation->create(
                $user_id,
                $date,
                $recommendation['recommended_savings'],
                $recommendation['reason']
            );
        }

        // Générer et sauvegarder les insights (nettoyer les anciens d'abord pour éviter la duplication)
        $this->financialInsight->clearUserInsights($user_id);
        $insights = $this->analyzeGoals($user_id);
        foreach ($insights as $insight) {
            $this->financialInsight->create(
                $user_id,
                $insight['type'],
                $insight['title'],
                $insight['message'],
                $insight['priority']
            );
        }

        return [
            'recommendation' => $recommendation,
            'insights' => $insights
        ];
    }

    /**
     * Récupère les dépenses pour une date spécifique
     */
    private function getExpensesForDate($user_id, $date)
    {
        $stmt = $this->pdo->prepare('SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = ? AND date = ?');
        $stmt->execute([$user_id, 'expense', $date]);
        $row = $stmt->fetch();
        return $row ? (float)$row['total'] : 0;
    }

    /**
     * Récupère les dépenses journalières pour un mois donné
     */
    private function getDailyExpensesForMonth($user_id, $year, $month)
    {
        $firstDay = sprintf('%04d-%02d-01', $year, $month);
        $lastDay = date('Y-m-t', strtotime($firstDay));
        $stmt = $this->pdo->prepare('SELECT date, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = ? AND date BETWEEN ? AND ? GROUP BY date');
        $stmt->execute([$user_id, 'expense', $firstDay, $lastDay]);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['date']] = (float)$row['total'];
        }
        return $result;
    }

    /**
     * Calcule le nombre de jours entre deux dates (positif si date2 est dans le futur, négatif si dans le passé)
     */
    private function daysBetween($date1, $date2)
    {
        $d1 = new DateTime($date1);
        $d2 = new DateTime($date2);
        $diff = $d1->diff($d2);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * Recalcule tout après une nouvelle transaction
     */
    public function recalculateAfterTransaction($user_id)
    {
        return $this->generateDailyInsights($user_id);
    }

    public function generateSavingBoard($user_id)
    {
        $profile = $this->userProfile->findByUserId($user_id);
        $goals = $this->goal->getActiveGoalsByUser($user_id);
        $recommendation = $this->calculateDailyRecommendation($user_id);

        $totalTarget = 0;
        $totalSaved = 0;
        $totalRemaining = 0;
        foreach ($goals as $goal) {
            $totalTarget += $goal['target_amount'];
            $totalSaved += $goal['saved_amount'];
            $totalRemaining += max(0, $goal['target_amount'] - $goal['saved_amount']);
        }

        $today = new DateTime();
        $year = (int)$today->format('Y');
        $month = (int)$today->format('n');
        $daysInMonth = (int)$today->format('t');
        $daysRemaining = $daysInMonth - (int)$today->format('j') + 1;
        $cells = [];
        $dailyBudget = $recommendation['daily_budget'] ?? 0;
        $monthlyExpensesByDay = $this->getDailyExpensesForMonth($user_id, $year, $month);
        $dailyGoalAmount = $daysRemaining > 0 ? round($totalRemaining / $daysRemaining, 2) : 0;
        $cellAmount = $recommendation['recommended_savings'] ?? 0;
        if ($cellAmount <= 0 && $dailyGoalAmount > 0) {
            $cellAmount = $dailyGoalAmount;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = DateTime::createFromFormat('Y-n-j', "$year-$month-$day");
            $formatted = $date->format('Y-m-d');
            $spent = $monthlyExpensesByDay[$formatted] ?? 0;
            $isToday = $formatted === $today->format('Y-m-d');
            $isPast = $date < $today;
            $completed = $isPast && $spent <= $dailyBudget && $spent > 0;
            $weekDay = (int)$date->format('N');
            $weekend = $weekDay >= 6;
            $amount = $cellAmount;
            $displayAmount = number_format($amount, 2);

            $cells[] = [
                'dayLabel' => $date->format('d'),
                'weekdayLabel' => $date->format('D'),
                'amount' => $displayAmount,
                'completed' => $completed,
                'today' => $isToday,
                'weekend' => $weekend,
                'date' => $formatted,
                'spent' => $spent
            ];
        }

        $progressPercent = $totalTarget > 0 ? round(($totalSaved / $totalTarget) * 100, 1) : 0;
        $daysRemaining = (int)date('t') - (int)$today->format('j') + 1;

        return [
            'monthLabel' => $today->format('F Y'),
            'stats' => [
                'saved' => number_format($totalSaved, 2),
                'goal' => number_format($totalTarget, 2),
                'remaining' => number_format(max(0, $totalRemaining), 2),
                'daysRemaining' => $daysRemaining,
                'progress' => $progressPercent,
                'totalGoals' => count($goals)
            ],
            'recommendation' => $recommendation,
            'cells' => $cells,
            'progressPercent' => $progressPercent
        ];
    }
}
