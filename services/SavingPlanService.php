<?php
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../services/RecommendationService.php';

class SavingPlanService
{
    private $pdo;
    private $goal;
    private $userProfile;
    private $transaction;
    private $recommendationService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->goal = new Goal($pdo);
        $this->userProfile = new UserProfile($pdo);
        $this->transaction = new Transaction($pdo);
        $this->recommendationService = new RecommendationService($pdo);
    }

    public function buildSmartSavingPlan($userId)
    {
        $profile = $this->userProfile->findByUserId($userId);
        $goals = $this->goal->getActiveGoalsByUser($userId);
        $recommendation = $this->recommendationService->calculateDailyRecommendation($userId);

        $totalTarget = 0;
        $totalSaved = 0;
        $totalRemaining = 0;
        foreach ($goals as $goal) {
            $totalTarget += $goal['target_amount'];
            $totalSaved += $goal['saved_amount'];
            $totalRemaining += max(0, $goal['target_amount'] - $goal['saved_amount']);
        }

        $plan = [
            'primaryObjective' => $this->findPrimaryGoal($goals),
            'recommendation' => $recommendation,
            'stats' => [
                'saved' => number_format($totalSaved, 2),
                'goal' => number_format($totalTarget, 2),
                'remaining' => number_format($totalRemaining, 2),
                'progress' => $totalTarget > 0 ? round(($totalSaved / $totalTarget) * 100, 1) : 0,
            ],
            'cells' => $this->buildDailyCells($recommendation, $totalRemaining),
        ];

        return $plan;
    }

    private function findPrimaryGoal($goals)
    {
        if (empty($goals)) {
            return ['name' => 'Aucun objectif actif', 'deadline' => null, 'remaining' => 0];
        }

        usort($goals, function ($a, $b) {
            $aDate = $a['deadline'] ? strtotime($a['deadline']) : PHP_INT_MAX;
            $bDate = $b['deadline'] ? strtotime($b['deadline']) : PHP_INT_MAX;
            return $aDate <=> $bDate;
        });

        $goal = $goals[0];
        $remaining = max(0, $goal['target_amount'] - $goal['saved_amount']);

        return [
            'name' => $goal['name'],
            'deadline' => $goal['deadline'] ? date('d/m/Y', strtotime($goal['deadline'])) : 'Sans date',
            'remaining' => number_format($remaining, 2),
            'progress' => $goal['target_amount'] > 0 ? round(($goal['saved_amount'] / $goal['target_amount']) * 100, 1) : 0,
        ];
    }

    private function buildDailyCells($recommendation, $totalRemaining)
    {
        $cells = [];
        $today = new DateTime();
        $year = (int) $today->format('Y');
        $month = (int) $today->format('n');
        $daysInMonth = (int) $today->format('t');
        $dailyAmount = $recommendation['recommended_savings'];
        if ($dailyAmount <= 0 && $daysInMonth > 0) {
            $dailyAmount = round($totalRemaining / $daysInMonth, 2);
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = DateTime::createFromFormat('Y-n-j', "$year-$month-$day");
            $formatted = $date->format('Y-m-d');
            $isToday = $formatted === $today->format('Y-m-d');
            $cells[] = [
                'date' => $formatted,
                'label' => $date->format('d'),
                'weekday' => $date->format('D'),
                'amount' => number_format($dailyAmount, 2),
                'today' => $isToday,
            ];
        }

        return $cells;
    }
}
