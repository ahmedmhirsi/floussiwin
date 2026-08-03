<?php
class UserProfile
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $monthly_salary, $additional_income = 0)
    {
        $stmt = $this->pdo->prepare('INSERT INTO user_profiles (user_id, monthly_salary, additional_income) VALUES (?, ?, ?)');
        return $stmt->execute([$user_id, $monthly_salary, $additional_income]);
    }

    public function findByUserId($user_id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM user_profiles WHERE user_id = ? LIMIT 1');
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    public function update($user_id, $monthly_salary, $additional_income)
    {
        $stmt = $this->pdo->prepare('UPDATE user_profiles SET monthly_salary = ?, additional_income = ? WHERE user_id = ?');
        return $stmt->execute([$monthly_salary, $additional_income, $user_id]);
    }

    public function getTotalMonthlyIncome($user_id)
    {
        $stmt = $this->pdo->prepare('SELECT total_monthly_income FROM user_profiles WHERE user_id = ? LIMIT 1');
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ? (float)$row['total_monthly_income'] : 0;
    }
}
