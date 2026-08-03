<?php
class Transaction
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $type, $amount, $category, $description, $date)
    {
        $stmt = $this->pdo->prepare('INSERT INTO transactions (user_id, type, amount, category, description, date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $res = $stmt->execute([$user_id, $type, $amount, $category, $description, $date]);
        if ($res) return (int)$this->pdo->lastInsertId();
        return false;
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM transactions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $type, $amount, $category, $description, $date)
    {
        $stmt = $this->pdo->prepare('UPDATE transactions SET type = ?, amount = ?, category = ?, description = ?, date = ? WHERE id = ?');
        return $stmt->execute([$type, $amount, $category, $description, $date, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM transactions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getByUser($user_id, $start = null, $end = null, $category = null, $search = null, $limit = null, $offset = null)
    {
        $sql = 'SELECT * FROM transactions WHERE user_id = ?';
        $params = [$user_id];

        if ($start) { $sql .= ' AND date >= ?'; $params[] = $start; }
        if ($end) { $sql .= ' AND date <= ?'; $params[] = $end; }
        if ($category) { $sql .= ' AND category = ?'; $params[] = $category; }
        if ($search) { $sql .= ' AND (description LIKE ? OR category LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

        $sql .= ' ORDER BY date DESC';
        if ($limit) { $sql .= ' LIMIT ' . (int)$limit; }
        if ($offset) { $sql .= ' OFFSET ' . (int)$offset; }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getMonthlySumByType($user_id, $type)
    {
        $firstDay = date('Y-m-01');
        $stmt = $this->pdo->prepare('SELECT SUM(amount) as s FROM transactions WHERE user_id = ? AND type = ? AND date >= ?');
        $stmt->execute([$user_id, $type, $firstDay]);
        $row = $stmt->fetch();
        return $row['s'] ? (float)$row['s'] : 0.0;
    }

    public function getCategorySumsThisMonth($user_id)
    {
        $firstDay = date('Y-m-01');
        $stmt = $this->pdo->prepare('SELECT category, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = ? AND date >= ? GROUP BY category');
        $stmt->execute([$user_id, 'expense', $firstDay]);
        return $stmt->fetchAll();
    }
}
