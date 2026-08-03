<?php
class Goal
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $name, $target_amount, $saved_amount, $deadline)
    {
        $stmt = $this->pdo->prepare('INSERT INTO goals (user_id, name, target_amount, saved_amount, deadline, status) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$user_id, $name, $target_amount, $saved_amount, $deadline, 'active']);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM goals WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $name, $target_amount, $saved_amount, $deadline)
    {
        $stmt = $this->pdo->prepare('UPDATE goals SET name = ?, target_amount = ?, saved_amount = ?, deadline = ? WHERE id = ?');
        return $stmt->execute([$name, $target_amount, $saved_amount, $deadline, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM goals WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getByUser($user_id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM goals WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function getActiveGoalsByUser($user_id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM goals WHERE user_id = ? AND status = ? ORDER BY id DESC');
        $stmt->execute([$user_id, 'active']);
        return $stmt->fetchAll();
    }
}
