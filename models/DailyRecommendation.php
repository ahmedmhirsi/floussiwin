<?php
class DailyRecommendation
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $date, $recommended_savings, $reason = null)
    {
        $stmt = $this->pdo->prepare('INSERT INTO daily_recommendations (user_id, date, recommended_savings, reason) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE recommended_savings = ?, reason = ?');
        return $stmt->execute([$user_id, $date, $recommended_savings, $reason, $recommended_savings, $reason]);
    }

    public function findByUserAndDate($user_id, $date)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM daily_recommendations WHERE user_id = ? AND date = ? LIMIT 1');
        $stmt->execute([$user_id, $date]);
        return $stmt->fetch();
    }

    public function updateActualSavings($user_id, $date, $actual_savings)
    {
        $stmt = $this->pdo->prepare('UPDATE daily_recommendations SET actual_savings = ? WHERE user_id = ? AND date = ?');
        return $stmt->execute([$actual_savings, $user_id, $date]);
    }

    public function getRecentRecommendations($user_id, $limit = 7)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM daily_recommendations WHERE user_id = ? ORDER BY date DESC LIMIT ' . (int)$limit);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}
