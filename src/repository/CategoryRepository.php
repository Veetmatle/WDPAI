<?php

require_once 'Repository.php';

class CategoryRepository extends Repository
{
    private static ?CategoryRepository $instance = null;

    public static function getInstance(): CategoryRepository
    {
        if (self::$instance === null) {
            self::$instance = new CategoryRepository();
        }
        return self::$instance;
    }

    public function getCategoriesByUserId(int $userId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, icon_name, color_hex
            FROM categories 
            WHERE user_id = :user_id
            ORDER BY name
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById(int $id, int $userId): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, icon_name, color_hex
            FROM categories 
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    public function createCategory(int $userId, string $name, string $iconName = 'category', string $colorHex = '#6B7280'): bool
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO categories (user_id, name, icon_name, color_hex) 
            VALUES (:user_id, :name, :icon_name, :color_hex)
        ');
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':icon_name', $iconName, PDO::PARAM_STR);
        $stmt->bindParam(':color_hex', $colorHex, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function deleteCategory(int $id, int $userId): bool
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM categories 
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function getDefaultCategories(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, icon_name, color_hex
            FROM categories 
            WHERE is_default = TRUE AND user_id IS NULL
            ORDER BY name
        ');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cloneDefaultCategoriesForUser(int $userId): bool
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO categories (user_id, name, icon_name, color_hex, is_default)
            SELECT :user_id, name, icon_name, color_hex, FALSE
            FROM categories
            WHERE is_default = TRUE AND user_id IS NULL
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function getExpensesByCategory(int $userId, int $month, int $year): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT c.name, c.icon_name, c.color_hex, 
                   COALESCE(SUM(ri.price * ri.quantity), 0) as total
            FROM categories c
            LEFT JOIN receipt_items ri ON ri.category_id = c.id
            LEFT JOIN receipts r ON r.id = ri.receipt_id 
                AND r.user_id = :user_id
                AND EXTRACT(MONTH FROM r.receipt_date) = :month
                AND EXTRACT(YEAR FROM r.receipt_date) = :year
            WHERE c.user_id = :user_id2
            GROUP BY c.id, c.name, c.icon_name, c.color_hex
            ORDER BY total DESC
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}