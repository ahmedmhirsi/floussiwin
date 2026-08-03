<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/FixedExpense.php';

class ForecastService
{
    private $pdo;
    private $transaction;
    private $userProfile;
    private $goal;
    private $fixedExpense;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->transaction = new Transaction($pdo);
        $this->userProfile = new UserProfile($pdo);
        $this->goal = new Goal($pdo);
        $this->fixedExpense = new FixedExpense($pdo);
    }

    public function getPredictions($userId)
    {
        $predictions = [];
        $profile = $this->userProfile->findByUserId($userId);
        $income = $profile ? $profile['total_monthly_income'] : 0;
        $expenses = $this->transaction->getMonthlySumByType($userId, 'expense');
        $incomeActual = $this->transaction->getMonthlySumByType($userId, 'income');
        $fixed = $this->fixedExpense->getTotalFixedExpenses($userId);
        $goals = $this->goal->getActiveGoalsByUser($userId);
        $activeCategory = $this->getTopCategory($userId);

        if ($income > 0) {
            $spentRatio = $expenses / $income;
            if ($spentRatio >= 0.9) {
                $predictions[] = [
                    'type' => 'warning',
                    'message' => 'Dans 8 jours votre budget pourrait devenir négatif si vous maintenez ce rythme.'
                ];
            }
            if ($expenses <= $income * 0.3) {
                $predictions[] = [
                    'type' => 'success',
                    'message' => 'Vous pourriez économiser environ ' . number_format(($income - $expenses) / 4, 2) . ' DT cette semaine.'
                ];
            }
        }

        if ($activeCategory) {
            $predictions[] = [
                'type' => 'info',
                'message' => 'Vous risquez de dépasser votre budget dans ' . $activeCategory['category'] . ' si vous continuez ce rythme.'
            ];
        }

        foreach ($goals as $goal) {
            $remaining = max(0, $goal['target_amount'] - $goal['saved_amount']);
            if ($goal['deadline']) {
                $daysLeft = $this->daysBetween(date('Y-m-d'), $goal['deadline']);
                if ($daysLeft > 0) {
                    $dailyNeeded = round($remaining / max(1, $daysLeft), 2);
                    $predictions[] = [
                        'type' => 'info',
                        'message' => 'L\'objectif ' . htmlspecialchars($goal['name']) . ' sera atteint le ' . date('d/m/Y', strtotime($goal['deadline'])) . ' si vous épargnez ' . $dailyNeeded . ' DT/jour.'
                    ];
                }
            }
        }

        if (empty($predictions)) {
            $predictions[] = [
                'type' => 'info',
                'message' => 'Aucun risque majeur détecté pour le moment. Continuez à suivre votre plan.'
            ];
        }

        return $predictions;
    }

    private function getTopCategory($userId)
    {
        $categories = $this->transaction->getCategorySumsThisMonth($userId);
        if (empty($categories)) return null;
        usort($categories, fn($a, $b) => $b['total'] <=> $a['total']);
        return $categories[0];
    }

    private function daysBetween($date1, $date2)
    {
        $d1 = new DateTime($date1);
        $d2 = new DateTime($date2);
        $diff = $d1->diff($d2);
        return $diff->invert ? -$diff->days : $diff->days;
    }
}
