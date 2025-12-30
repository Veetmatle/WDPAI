<?php

require_once 'Repository.php';
require_once __DIR__ . '/../model/Receipt.php';
require_once __DIR__ . '/../model/ReceiptItem.php';

class ReceiptRepository extends Repository
{
    private static ?ReceiptRepository $instance = null;

    public static function getInstance(): ReceiptRepository
    {
        if (self::$instance === null) {
            self::$instance = new ReceiptRepository();
        }
        return self::$instance;
    }

    public function getReceiptsByUserId(int $userId, int $limit = 100): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, user_id, store_name, receipt_date, total_amount, receipt_image_path, notes, created_at
            FROM receipts 
            WHERE user_id = :user_id 
            ORDER BY receipt_date DESC, created_at DESC
            LIMIT :limit
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentReceipts(int $userId, int $limit = 5): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT r.id, r.store_name, r.receipt_date, r.total_amount, r.notes,
                   (SELECT c.name FROM receipt_items ri2 
                    JOIN categories c ON c.id = ri2.category_id 
                    WHERE ri2.receipt_id = r.id LIMIT 1) as category_name,
                   (SELECT c.icon_name FROM receipt_items ri3 
                    JOIN categories c ON c.id = ri3.category_id 
                    WHERE ri3.receipt_id = r.id LIMIT 1) as category_icon
            FROM receipts r
            WHERE r.user_id = :user_id
            ORDER BY r.receipt_date DESC, r.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReceiptsByMonth(int $userId, int $month, int $year, int $limit = 5): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT r.id, r.store_name, r.receipt_date, r.total_amount, r.notes,
                   (SELECT c.name FROM receipt_items ri2 
                    JOIN categories c ON c.id = ri2.category_id 
                    WHERE ri2.receipt_id = r.id LIMIT 1) as category_name,
                   (SELECT c.icon_name FROM receipt_items ri3 
                    JOIN categories c ON c.id = ri3.category_id 
                    WHERE ri3.receipt_id = r.id LIMIT 1) as category_icon
            FROM receipts r
            WHERE r.user_id = :user_id 
              AND EXTRACT(MONTH FROM r.receipt_date) = :month
              AND EXTRACT(YEAR FROM r.receipt_date) = :year
            ORDER BY r.receipt_date DESC, r.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReceiptById(int $id, int $userId): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, user_id, store_name, receipt_date, total_amount, receipt_image_path, notes, created_at
            FROM receipts 
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
        return $receipt ?: null;
    }

    public function getReceiptsByDate(int $userId, string $date): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT r.id, r.store_name, r.receipt_date, r.total_amount, r.notes, r.created_at,
                   COALESCE(
                       (SELECT c.icon_name FROM receipt_items ri 
                        JOIN categories c ON c.id = ri.category_id 
                        WHERE ri.receipt_id = r.id LIMIT 1), 
                       \'receipt_long\'
                   ) as icon_name
            FROM receipts r
            WHERE r.user_id = :user_id AND r.receipt_date = :date
            ORDER BY r.created_at DESC
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDailyTotal(int $userId, string $date): float
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM receipts 
            WHERE user_id = :user_id AND receipt_date = :date
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function getMonthlyExpensesByDay(int $userId, int $month, int $year): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT receipt_date, SUM(total_amount) as daily_total
            FROM receipts 
            WHERE user_id = :user_id 
              AND EXTRACT(MONTH FROM receipt_date) = :month
              AND EXTRACT(YEAR FROM receipt_date) = :year
            GROUP BY receipt_date
            ORDER BY receipt_date
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['receipt_date']] = (float) $row['daily_total'];
        }
        return $result;
    }

    public function getMonthlyTotal(int $userId, int $month, int $year): float
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM receipts 
            WHERE user_id = :user_id 
              AND EXTRACT(MONTH FROM receipt_date) = :month
              AND EXTRACT(YEAR FROM receipt_date) = :year
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function getExpensesSummary(int $userId, int $months = 4): array
    {
        $results = [];
        $conn = $this->database->connect();
        
        for ($i = 0; $i < $months; $i++) {
            $date = new DateTime();
            $date->modify("-{$i} months");
            $month = (int)$date->format('n');
            $year = (int)$date->format('Y');
            
            $stmt = $conn->prepare('
                SELECT COALESCE(SUM(total_amount), 0) as total
                FROM receipts 
                WHERE user_id = :user_id 
                  AND EXTRACT(MONTH FROM receipt_date) = :month
                  AND EXTRACT(YEAR FROM receipt_date) = :year
            ');
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            
            $results[] = [
                'month' => $month,
                'year' => $year,
                'total' => (float)$stmt->fetchColumn()
            ];
        }
        
        return $results;
    }

    public function createReceipt(int $userId, string $storeName, string $date, float $totalAmount, ?string $imagePath = null, ?string $notes = null): int
    {
        $conn = $this->database->connect();
        
        $stmt = $conn->prepare('
            INSERT INTO receipts (user_id, store_name, receipt_date, total_amount, receipt_image_path, notes) 
            VALUES (:user_id, :store_name, :receipt_date, :total_amount, :receipt_image_path, :notes)
            RETURNING id
        ');
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':store_name', $storeName, PDO::PARAM_STR);
        $stmt->bindParam(':receipt_date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':total_amount', $totalAmount, PDO::PARAM_STR);
        $stmt->bindParam(':receipt_image_path', $imagePath, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }

    public function deleteReceipt(int $id, int $userId): bool
    {
        $conn = $this->database->connect();
        
        try {
            $conn->beginTransaction();
            
            $stmtItems = $conn->prepare('
                DELETE FROM receipt_items 
                WHERE receipt_id = :receipt_id
            ');
            $stmtItems->bindParam(':receipt_id', $id, PDO::PARAM_INT);
            $stmtItems->execute();
            
            $stmtReceipt = $conn->prepare('
                DELETE FROM receipts 
                WHERE id = :id AND user_id = :user_id
            ');
            $stmtReceipt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtReceipt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtReceipt->execute();
            
            $deleted = $stmtReceipt->rowCount() > 0;
            
            $conn->commit();
            return $deleted;
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log('Delete receipt error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getReceiptItems(int $receiptId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT ri.id, ri.product_name, ri.price, ri.quantity,
                   c.id as category_id, c.name as category_name, c.icon_name, c.color_hex
            FROM receipt_items ri
            LEFT JOIN categories c ON c.id = ri.category_id
            WHERE ri.receipt_id = :receipt_id
            ORDER BY ri.id
        ');
        $stmt->bindParam(':receipt_id', $receiptId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addReceiptItem(int $receiptId, string $productName, float $price, ?int $categoryId = null, int $quantity = 1): bool
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO receipt_items (receipt_id, product_name, category_id, price, quantity) 
            VALUES (:receipt_id, :product_name, :category_id, :price, :quantity)
        ');
        
        $stmt->bindParam(':receipt_id', $receiptId, PDO::PARAM_INT);
        $stmt->bindParam(':product_name', $productName, PDO::PARAM_STR);
        
        if ($categoryId === null) {
            $stmt->bindValue(':category_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function updateReceiptTotal(int $receiptId): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE receipts 
            SET total_amount = (
                SELECT COALESCE(SUM(price * quantity), 0) 
                FROM receipt_items 
                WHERE receipt_id = :receipt_id
            )
            WHERE id = :receipt_id2
        ');
        
        $stmt->bindParam(':receipt_id', $receiptId, PDO::PARAM_INT);
        $stmt->bindParam(':receipt_id2', $receiptId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function updateReceipt(int $receiptId, int $userId, string $storeName, string $date, float $totalAmount, ?string $notes = null): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE receipts 
            SET store_name = :store_name, receipt_date = :receipt_date, total_amount = :total_amount, notes = :notes
            WHERE id = :id AND user_id = :user_id
        ');
        
        $stmt->bindParam(':id', $receiptId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':store_name', $storeName, PDO::PARAM_STR);
        $stmt->bindParam(':receipt_date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':total_amount', $totalAmount, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function deleteReceiptItems(int $receiptId): bool
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM receipt_items WHERE receipt_id = :receipt_id
        ');
        $stmt->bindParam(':receipt_id', $receiptId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getExpensesByCategory(int $userId, int $month, int $year): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT 
                COALESCE(c.name, \'Bez kategorii\') as category_name,
                COALESCE(c.icon_name, \'category\') as icon_name,
                COALESCE(c.color_hex, \'#64748B\') as color_hex,
                SUM(
                    CASE 
                        WHEN ri.id IS NOT NULL THEN ri.price * ri.quantity
                        ELSE r.total_amount
                    END
                ) as total
            FROM receipts r
            LEFT JOIN receipt_items ri ON ri.receipt_id = r.id
            LEFT JOIN categories c ON c.id = ri.category_id
            WHERE r.user_id = :user_id
              AND EXTRACT(MONTH FROM r.receipt_date) = :month
              AND EXTRACT(YEAR FROM r.receipt_date) = :year
            GROUP BY c.id, c.name, c.icon_name, c.color_hex
            ORDER BY total DESC
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyExpensesByCategory(int $userId, int $months = 6): array
    {
        $results = [];
        $conn = $this->database->connect();
        
        for ($i = 0; $i < $months; $i++) {
            $date = new DateTime();
            $date->modify("-{$i} months");
            $month = (int)$date->format('n');
            $year = (int)$date->format('Y');
            
            $stmt = $conn->prepare('
                SELECT 
                    COALESCE(c.name, \'Bez kategorii\') as category_name,
                    COALESCE(c.color_hex, \'#64748B\') as color_hex,
                    SUM(
                        CASE 
                            WHEN ri.id IS NOT NULL THEN ri.price * ri.quantity
                            ELSE r.total_amount
                        END
                    ) as total
                FROM receipts r
                LEFT JOIN receipt_items ri ON ri.receipt_id = r.id
                LEFT JOIN categories c ON c.id = ri.category_id
                WHERE r.user_id = :user_id
                  AND EXTRACT(MONTH FROM r.receipt_date) = :month
                  AND EXTRACT(YEAR FROM r.receipt_date) = :year
                GROUP BY c.id, c.name, c.color_hex
            ');
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            
            $results[] = [
                'month' => $month,
                'year' => $year,
                'categories' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        }
        
        return $results;
    }
}