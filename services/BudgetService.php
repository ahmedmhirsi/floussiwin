<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/FixedExpense.php';

class BudgetService
{
    private $pdo;
    private $transaction;
    private $userProfile;
    private $fixedExpense;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->transaction = new Transaction($pdo);
        $this->userProfile = new UserProfile($pdo);
        $this->fixedExpense = new FixedExpense($pdo);
    }

    public function getMonthlySummary($userId)
    {
        $income = $this->transaction->getMonthlySumByType($userId, 'income');
        $expense = $this->transaction->getMonthlySumByType($userId, 'expense');
        $fixed = $this->fixedExpense->getTotalFixedExpenses($userId);
        $profile = $this->userProfile->findByUserId($userId);

        return [
            'income' => $income,
            'expense' => $expense,
            'fixedExpenses' => $fixed,
            'available' => max(0, $income - $expense),
            'netAfterFixed' => max(0, $income - $fixed),
            'incomeConfigured' => $profile ? true : false,
        ];
    }

    public function getAvailableToday($userId)
    {
        $summary = $this->getMonthlySummary($userId);
        $daysInMonth = (int) date('t');
        $todayDay = (int) date('j');
        $remainingDays = max(1, $daysInMonth - $todayDay + 1);

        $availablePerDay = $remainingDays > 0 ? $summary['available'] / $remainingDays : 0;

        return [
            'availableToday' => round($availablePerDay, 2),
            'remainingDays' => $remainingDays,
            'monthlyRemaining' => $summary['available'],
        ];
    }

    public function getUpcomingBills($userId, $limit = 3)
    {
        $fixedExpenses = $this->fixedExpense->findByUserId($userId);
        $bills = [];
        foreach ($fixedExpenses as $expense) {
            $bills[] = [
                'name' => $expense['name'],
                'amount' => (float) $expense['amount'],
                'category' => $expense['category'],
                'dueDate' => date('d/m/Y', strtotime('+'.rand(1, 15).' days')),
            ];
        }
        return array_slice($bills, 0, $limit);
    }

    public function getMonthlyBudgetByCategory($userId)
    {
        return $this->transaction->getCategorySumsThisMonth($userId);
    }
}
