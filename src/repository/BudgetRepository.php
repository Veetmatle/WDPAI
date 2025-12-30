<?php

require_once 'Repository.php';

class BudgetRepository extends Repository
{
    private static ?BudgetRepository $instance = null;

    public static function getInstance(): BudgetRepository
    {
        if (self::$instance === null) {
            self::$instance = new BudgetRepository();
        }
        return self::$instance;
    }

    public function getBudget(int $userId, int $month, int $year): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, user_id, month, year, amount_limit, created_at
            FROM budgets 
            WHERE user_id = :user_id AND month = :month AND year = :year
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        $budget = $stmt->fetch(PDO::FETCH_ASSOC);
        return $budget ?: null;
    }

    public function getCurrentBudget(int $userId): ?array
    {
        $month = (int) date('n');
        $year = (int) date('Y');
        return $this->getBudget($userId, $month, $year);
    }

    public function setBudget(int $userId, int $month, int $year, float $amountLimit): bool
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO budgets (user_id, month, year, amount_limit) 
            VALUES (:user_id, :month, :year, :amount_limit)
            ON CONFLICT (user_id, month, year) 
            DO UPDATE SET amount_limit = :amount_limit2
        ');
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':amount_limit', $amountLimit, PDO::PARAM_STR);
        $stmt->bindParam(':amount_limit2', $amountLimit, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function getAllBudgets(int $userId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, month, year, amount_limit, created_at
            FROM budgets 
            WHERE user_id = :user_id 
            ORDER BY year DESC, month DESC
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteBudget(int $id, int $userId): bool
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM budgets 
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function getBudgetStatus(int $userId, int $month, int $year, float $currentExpenses): array
    {
        $budget = $this->getBudget($userId, $month, $year);
        
        if (!$budget) {
            return [
                'has_budget' => false,
                'limit' => 0,
                'spent' => $currentExpenses,
                'remaining' => 0,
                'percentage' => 0
            ];
        }

        $limit = (float) $budget['amount_limit'];
        $remaining = $limit - $currentExpenses;
        $percentage = $limit > 0 ? min(100, ($currentExpenses / $limit) * 100) : 0;

        return [
            'has_budget' => true,
            'limit' => $limit,
            'spent' => $currentExpenses,
            'remaining' => $remaining,
            'percentage' => round($percentage, 1)
        ];
    }
}