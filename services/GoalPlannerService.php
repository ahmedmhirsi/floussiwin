<?php
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/FixedExpense.php';

class GoalPlannerService
{
    private $pdo;
    private $goal;
    private $userProfile;
    private $fixedExpense;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->goal = new Goal($pdo);
        $this->userProfile = new UserProfile($pdo);
        $this->fixedExpense = new FixedExpense($pdo);
    }

    public function getGoalStrategy($userId)
    {
        $goals = $this->goal->getActiveGoalsByUser($userId);
        if (empty($goals)) {
            return [
                'priority' => null,
                'recommendation' => 'Créez un objectif pour recevoir un plan de priorisation personnalisé.',
                'details' => []
            ];
        }

        usort($goals, fn($a, $b) => $this->goalPriorityScore($b) <=> $this->goalPriorityScore($a));
        $primary = $goals[0];
        $secondary = $goals[1] ?? null;

        $details = [
            'primary' => [
                'name' => $primary['name'],
                'remaining' => number_format(max(0, $primary['target_amount'] - $primary['saved_amount']), 2),
                'deadline' => $primary['deadline'] ? date('d/m/Y', strtotime($primary['deadline'])) : 'Sans date',
            ]
        ];

        if ($secondary) {
            $details['secondary'] = [
                'name' => $secondary['name'],
                'remaining' => number_format(max(0, $secondary['target_amount'] - $secondary['saved_amount']), 2),
                'deadline' => $secondary['deadline'] ? date('d/m/Y', strtotime($secondary['deadline'])) : 'Sans date',
            ];
        }

        return [
            'priority' => $primary['name'],
            'recommendation' => 'Priorisez l\'objectif "' . $primary['name'] . '" et reportez temporairement les autres si nécessaire.',
            'details' => $details,
        ];
    }

    private function goalPriorityScore($goal)
    {
        $progress = $goal['target_amount'] > 0 ? $goal['saved_amount'] / $goal['target_amount'] : 0;
        $deadline = $goal['deadline'] ? strtotime($goal['deadline']) : PHP_INT_MAX;
        return (1 - $progress) * 1000000 + ($deadline === PHP_INT_MAX ? 0 : 1 / max(1, $deadline));
    }
}
