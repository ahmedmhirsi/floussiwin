<?php
class FinancialInsight
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $insight_type, $title, $message, $priority = 1)
    {
        $stmt = $this->pdo->prepare('INSERT INTO financial_insights (user_id, insight_type, title, message, priority) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$user_id, $insight_type, $title, $message, $priority]);
    }

    public function findByUserId($user_id, $limit = 10)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM financial_insights WHERE user_id = ? AND is_dismissed = 0 ORDER BY priority DESC, created_at DESC LIMIT ' . (int)$limit);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function dismiss($id)
    {
        $stmt = $this->pdo->prepare('UPDATE financial_insights SET is_dismissed = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function clearOldInsights($user_id, $days = 30)
    {
        $stmt = $this->pdo->prepare('DELETE FROM financial_insights WHERE user_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL ' . (int)$days . ' DAY)');
        return $stmt->execute([$user_id]);
    }

    public function clearUserInsights($user_id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM financial_insights WHERE user_id = ?');
        return $stmt->execute([$user_id]);
    }
}
