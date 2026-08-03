<?php
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/FixedExpense.php';
require_once __DIR__ . '/../models/Goal.php';

class FinancialScoreService
{
    private $pdo;
    private $userProfile;
    private $transaction;
    private $fixedExpense;
    private $goal;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userProfile = new UserProfile($pdo);
        $this->transaction = new Transaction($pdo);
        $this->fixedExpense = new FixedExpense($pdo);
        $this->goal = new Goal($pdo);
    }

    public function calculateScores($userId)
    {
        $profile = $this->userProfile->findByUserId($userId);
        $income = $profile ? $profile['total_monthly_income'] : 0;
        $expenses = $this->transaction->getMonthlySumByType($userId, 'expense');
        $fixed = $this->fixedExpense->getTotalFixedExpenses($userId);
        $goals = $this->goal->getActiveGoalsByUser($userId);

        $cashflowScore = $this->scoreCashflow($income, $fixed, $expenses);
        $budgetScore = $this->scoreBudget($income, $expenses);
        $savingsScore = $this->scoreSavings($income, $expenses);
        $goalsScore = $this->scoreGoals($goals);
        $habitsScore = $this->scoreHabits($income, $expenses, $fixed);

        $overall = round(($cashflowScore + $budgetScore + $savingsScore + $goalsScore + $habitsScore) / 5, 0);

        return [
            'cashflowScore' => $cashflowScore,
            'budgetScore' => $budgetScore,
            'savingsScore' => $savingsScore,
            'goalsScore' => $goalsScore,
            'habitsScore' => $habitsScore,
            'overallScore' => $overall,
            'explanations' => [
                'cashflow' => $this->explainCashflow($cashflowScore, $income, $fixed),
                'budget' => $this->explainBudget($budgetScore, $expenses, $income),
                'savings' => $this->explainSavings($savingsScore, $expenses, $income),
                'goals' => $this->explainGoals($goalsScore, $goals),
                'habits' => $this->explainHabits($habitsScore),
            ],
        ];
    }

    private function scoreCashflow($income, $fixed, $expenses)
    {
        if ($income <= 0) return 0;
        $surplus = max(0, $income - $fixed - $expenses);
        $ratio = $income > 0 ? $surplus / $income : 0;
        return min(100, round($ratio * 120, 0));
    }

    private function scoreBudget($income, $expenses)
    {
        if ($income <= 0) return 0;
        $ratio = $expenses / $income;
        if ($ratio <= 0.5) return 100;
        if ($ratio <= 0.7) return 80;
        if ($ratio <= 0.85) return 60;
        if ($ratio <= 1.0) return 40;
        return 20;
    }

    private function scoreSavings($income, $expenses)
    {
        if ($income <= 0) return 0;
        $saved = max(0, $income - $expenses);
        $ratio = $saved / $income;
        return min(100, round($ratio * 100, 0));
    }

    private function scoreGoals($goals)
    {
        if (empty($goals)) return 50;
        $scores = [];
        foreach ($goals as $goal) {
            $progress = $goal['target_amount'] > 0 ? min(100, round(($goal['saved_amount'] / $goal['target_amount']) * 100, 0)) : 0;
            $scores[] = $progress;
        }
        return round(array_sum($scores) / count($scores), 0);
    }

    private function scoreHabits($income, $expenses, $fixed)
    {
        if ($income <= 0) return 0;
        $discretionary = max(0, $income - $fixed);
        if ($discretionary <= 0) return 10;
        $ratio = $expenses / $discretionary;
        if ($ratio <= 0.6) return 100;
        if ($ratio <= 0.8) return 80;
        if ($ratio <= 1.0) return 50;
        return 25;
    }

    private function explainCashflow($score, $income, $fixed)
    {
        if ($income <= 0) return 'Ajoutez vos revenus pour commencer votre analyse de trésorerie.';
        if ($score >= 80) return 'Votre trésorerie est solide : vous avez une marge confortable après vos charges fixes.';
        if ($score >= 50) return 'La trésorerie est acceptable, mais surveillez vos charges fixes ce mois.';
        return 'Vos charges fixes réduisent fortement votre marge. Réduisez ou réévaluez vos engagements.';
    }

    private function explainBudget($score, $expenses, $income)
    {
        if ($income <= 0) return 'Votre budget n\'est pas encore défini.';
        if ($score >= 80) return 'Votre budget est bien maîtrisé ce mois : vous dépensez moins de 70% de vos revenus.';
        if ($score >= 50) return 'Vous dépensez entre 70% et 85% de vos revenus. Restez vigilant sur les dépenses discrétionnaires.';
        return 'Votre budget est tendu. Identifiez les catégories à réduire rapidement.';
    }

    private function explainSavings($score, $expenses, $income)
    {
        if ($income <= 0) return 'Votre capacité d\'épargne sera calculée après saisie de vos revenus.';
        if ($score >= 80) return 'Votre potentiel d\'épargne est très bon. Continuez ainsi.';
        if ($score >= 50) return 'Vous pouvez épargner, mais choisissez des dépenses à réduire pour accélérer le rythme.';
        return 'Vous êtes au minimum. Augmentez vos économies en limitant les achats non essentiels.';
    }

    private function explainGoals($score, $goals)
    {
        if (empty($goals)) return 'Aucun objectif actif. Définissez-en un pour donner un sens à votre plan.';
        if ($score >= 80) return 'Vos objectifs avancent bien. Continuez à respecter vos échéances.';
        if ($score >= 50) return 'Vos objectifs progressent, mais certains ont besoin d\'une attention supplémentaire.';
        return 'Certains objectifs sont en retard. Réévaluez vos priorités ou réajustez votre plan.';
    }

    private function explainHabits($score)
    {
        if ($score >= 80) return 'Vos habitudes de dépenses sont saines. Vous respectez votre budget. ';
        if ($score >= 50) return 'Vos habitudes sont moyennes. Evitez les achats impulsifs.';
        return 'Vos habitudes sont déséquilibrées. Cherchez les dépenses superflues à couper.';
    }
}
