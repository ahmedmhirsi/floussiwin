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
        // normalize recommendation keys consumed by the view
        $rec = $recommendation ?: [];
        $rec['daily_budget'] = isset($rec['daily_budget']) ? round((float)$rec['daily_budget'], 2) : 0.00;
        // some services expose average_daily_expense; map it to yesterday_expenses for the view
        $rec['yesterday_expenses'] = isset($rec['average_daily_expense']) ? round((float)$rec['average_daily_expense'], 2) : ($rec['daily_budget'] ?? 0.00);
        $rec['recommended_savings'] = isset($rec['recommended_savings']) ? round((float)$rec['recommended_savings'], 2) : 0.00;
        $rec['reason'] = isset($rec['reason']) ? $rec['reason'] : '';
        $recommendation = $rec;

        $totalTarget = 0;
        $totalSaved = 0;
        $totalRemaining = 0;
        foreach ($goals as $goal) {
            $totalTarget += $goal['target_amount'];
            $totalSaved += $goal['saved_amount'];
            $totalRemaining += max(0, $goal['target_amount'] - $goal['saved_amount']);
        }

        $today = new \DateTime();
        $daysInMonth = (int)$today->format('t');
        $daysRemaining = $daysInMonth - (int)$today->format('j') + 1;
        $progressPercent = $totalTarget > 0 ? round(($totalSaved / $totalTarget) * 100, 1) : 0;

        $plan = [
            'primaryObjective' => $this->findPrimaryGoal($goals),
            'recommendation' => $recommendation,
            // Stats structured like the original engine to keep views stable
            'stats' => [
                'saved' => number_format($totalSaved, 2),
                'goal' => number_format($totalTarget, 2),
                'remaining' => number_format($totalRemaining, 2),
                'daysRemaining' => $daysRemaining,
                'progress' => $progressPercent,
                'totalGoals' => count($goals)
            ],
            'monthLabel' => $today->format('F Y'),
            'cells' => $this->buildDailyCells($userId, $recommendation, $totalRemaining),
            'progressPercent' => $progressPercent,
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

    private function buildDailyCells($userId, $recommendation, $totalRemaining)
    {
        $cells = [];
        $today = new DateTime();
        $year = (int) $today->format('Y');
        $month = (int) $today->format('n');
        $daysInMonth = (int) $today->format('t');
        // If there's something left to save for goals, distribute it across days
        // with deterministic variability per user/month so values don't change on refresh.
        if ($totalRemaining > 0 && $daysInMonth > 0) {
            $weights = [];
            $sumW = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $tmpDate = DateTime::createFromFormat('Y-n-j', "$year-$month-$d");
                $wday = (int)$tmpDate->format('N');
                // derive a deterministic int from user/month/day using sha256
                $h = substr(hash('sha256', "{$userId}-{$year}-{$month}-{$d}"), 0, 8);
                $randInt = hexdec($h) % 1000; // 0..999
                if ($wday >= 6) {
                    // weekend: smaller base (10..80)
                    $base = 10 + ($randInt % 71);
                } else {
                    // weekday: larger base (40..160)
                    $base = 40 + ($randInt % 121);
                }
                $weights[$d] = $base;
                $sumW += $base;
            }

            // compute raw amounts from weights deterministically
            $amounts = [];
            $remaining = round((float)$totalRemaining, 2);
            $allocated = 0.0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                if ($d == $daysInMonth) {
                    $amt = round($remaining - $allocated, 2);
                } else {
                    $share = $weights[$d] / $sumW;
                    $amt = round(max(0.01, $remaining * $share), 2);
                    $allocated += $amt;
                }
                $amounts[$d] = $amt;
            }

            // build cells with variable but deterministic amounts
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = DateTime::createFromFormat('Y-n-j', "$year-$month-$day");
                $formatted = $date->format('Y-m-d');
                $isToday = $formatted === $today->format('Y-m-d');
                $weekDay = (int)$date->format('N');
                $weekend = $weekDay >= 6;
                $cells[] = [
                    'date' => $formatted,
                    'dayLabel' => $date->format('d'),
                    'weekdayLabel' => $date->format('D'),
                    'amount' => number_format($amounts[$day], 2),
                    'completed' => false,
                    'today' => $isToday,
                    'weekend' => $weekend,
                    'spent' => 0,
                ];
            }
        } else {
            // fallback: uniform daily amount (as before)
            $dailyAmount = $recommendation['recommended_savings'];
            if ($dailyAmount <= 0 && $daysInMonth > 0) {
                $dailyAmount = round($totalRemaining / $daysInMonth, 2);
            }

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = DateTime::createFromFormat('Y-n-j', "$year-$month-$day");
                $formatted = $date->format('Y-m-d');
                $isToday = $formatted === $today->format('Y-m-d');
                $weekDay = (int)$date->format('N');
                $weekend = $weekDay >= 6;
                $cells[] = [
                    'date' => $formatted,
                    'dayLabel' => $date->format('d'),
                    'weekdayLabel' => $date->format('D'),
                    'amount' => number_format($dailyAmount, 2),
                    'completed' => false,
                    'today' => $isToday,
                    'weekend' => $weekend,
                    'spent' => 0,
                ];
            }
        }

        return $cells;
    }
}
