<?php
class FixedExpense
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $name, $amount, $category = null)
    {
        $stmt = $this->pdo->prepare('INSERT INTO fixed_expenses (user_id, name, amount, category) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$user_id, $name, $amount, $category]);
    }

    public function findByUserId($user_id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM fixed_expenses WHERE user_id = ? AND is_active = 1 ORDER BY id DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function update($id, $name, $amount, $category = null)
    {
        $stmt = $this->pdo->prepare('UPDATE fixed_expenses SET name = ?, amount = ?, category = ? WHERE id = ?');
        return $stmt->execute([$name, $amount, $category, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('UPDATE fixed_expenses SET is_active = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getTotalFixedExpenses($user_id)
    {
        $stmt = $this->pdo->prepare('SELECT SUM(amount) as total FROM fixed_expenses WHERE user_id = ? AND is_active = 1');
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ? (float)$row['total'] : 0;
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM fixed_expenses WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
