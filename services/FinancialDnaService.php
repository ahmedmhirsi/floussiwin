<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Goal.php';

class FinancialDnaService
{
    private $pdo;
    private $transaction;
    private $goal;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->transaction = new Transaction($pdo);
        $this->goal = new Goal($pdo);
    }

    public function analyze($userId)
    {
        $categories = $this->transaction->getCategorySumsThisMonth($userId);
        $goals = $this->goal->getActiveGoalsByUser($userId);
        $spending = array_sum(array_column($categories, 'total'));
        $goalCount = count($goals);
        $top = $this->getTopCategory($categories);

        if ($top && stripos($top['category'], 'Café') !== false) {
            return $this->buildProfile('Impulsive Buyer', 'Vous avez un goût pour les micropaiements quotidiens. Le système vous aidera à fixer des règles claires.');
        }

        if ($top && stripos($top['category'], 'Loisirs') !== false && $spending > 0) {
            return $this->buildProfile('Big Spender', 'Vous dépensez beaucoup dans les plaisirs. Le copilote va prioriser les économies sans sacrifier votre confort.');
        }

        if ($goalCount >= 2 && $spending > 0) {
            return $this->buildProfile('Planner', 'Vous planifiez plusieurs objectifs en même temps. Le copilote va vous aider à les organiser et prioriser.');
        }

        if ($spending > 0 && $top && stripos($top['category'], 'Loyer') !== false) {
            return $this->buildProfile('Saver', 'Vous êtes centré sur la sécurité et les charges essentielles. Le copilote va optimiser vos économies stables.');
        }

        return $this->buildProfile('Minimalist', 'Vous gardez un profil prudent. Le système renforcera vos meilleures habitudes et vous encouragera à investir dans l\'essentiel.');
    }

    private function getTopCategory($categories)
    {
        if (empty($categories)) return null;
        usort($categories, fn($a, $b) => $b['total'] <=> $a['total']);
        return $categories[0];
    }

    private function buildProfile($label, $description)
    {
        return [
            'label' => $label,
            'description' => $description,
            'traits' => $this->profileTraits($label)
        ];
    }

    private function profileTraits($label)
    {
        return match ($label) {
            'Planner' => ['Objectifs multiples', 'Prévision', 'Organisation'],
            'Saver' => ['Prudent', 'Sécuritaire', 'Constant'],
            'Impulsive Buyer' => ['Spontané', 'Basé sur l\'émotion', 'A besoin de structure'],
            'Big Spender' => ['Lifestyle', 'Dépenses de plaisir', 'Risque élevé'],
            default => ['Simple', 'Économe', 'Contrôlé'],
        };
    }
}
