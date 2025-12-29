<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';
require_once __DIR__ . '/../repository/BudgetRepository.php';
require_once __DIR__ . '/../repository/CategoryRepository.php';
require_once __DIR__ . '/../services/OCRService.php';

/**
 * API Controller for JS
 * Handles JSON API endpoints for AJAX request (json responses not rendered views)
 */
class ApiController extends AppController
{
    private ReceiptRepository $receiptRepository;
    private BudgetRepository $budgetRepository;
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
        $this->budgetRepository = BudgetRepository::getInstance();
        $this->categoryRepository = CategoryRepository::getInstance();
    }

    /**
     * Get monthly expenses
     */
    public function monthlyExpenses(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        $total = $this->receiptRepository->getMonthlyTotal($userId, $month, $year);

        $this->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'total' => $total
            ]
        ]);
    }

    /**
     * Get daily expenses for a date
     */
    public function dailyExpenses(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy format daty'], 400);
            return;
        }

        $receipts = $this->receiptRepository->getReceiptsByDate($userId, $date);
        $total = $this->receiptRepository->getDailyTotal($userId, $date);

        $this->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'total' => $total,
                'receipts' => $receipts
            ]
        ]);
    }

    /**
     * Get budget status
     */
    public function budgetStatus(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        $monthlyTotal = $this->receiptRepository->getMonthlyTotal($userId, $month, $year);
        $budgetStatus = $this->budgetRepository->getBudgetStatus($userId, $month, $year, $monthlyTotal);

        $this->json([
            'success' => true,
            'data' => $budgetStatus
        ]);
    }

    /**
     * Get categories
     */
    public function categories(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $categories = $this->categoryRepository->getCategoriesByUserId($userId);

        $this->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Process OCR from uploaded receipt image
     */
    public function ocrProcess(): void
    {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowa metoda'], 405);
            return;
        }

        if (!isset($_FILES['receipt_image']) || $_FILES['receipt_image']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'Nie przesłano pliku'], 400);
            return;
        }

        $file = $_FILES['receipt_image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy typ pliku'], 400);
            return;
        }

        // Validate file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $this->json(['success' => false, 'error' => 'Plik jest za duży (max 10MB)'], 400);
            return;
        }

        try {
            // Save file
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('receipt_', true) . '.' . $extension;
            $uploadDir = __DIR__ . '/../../public/uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $filename;
            move_uploaded_file($file['tmp_name'], $uploadPath);

            // Process OCR
            $ocrService = new OCRService();
            $ocrResult = $ocrService->processReceipt($uploadPath);

            $this->json([
                'success' => true,
                'data' => array_merge([
                    'image_path' => '/uploads/' . $filename
                ], $ocrResult)
            ]);
        } catch (Exception $e) {
            error_log("OCR processing error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Błąd przetwarzania obrazu'], 500);
        }
    }

    /**
     * Save receipt from form
     */
    public function saveReceipt(): void
    {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowa metoda'], 405);
            return;
        }

        $userId = $this->getUserId();
        $data = $this->getJsonBody();

        $storeName = $this->sanitize($data['store_name'] ?? '');
        $date = $data['date'] ?? date('Y-m-d');
        $totalAmount = (float) ($data['total_amount'] ?? 0);
        $notes = $this->sanitize($data['notes'] ?? '');
        $imagePath = $data['image_path'] ?? null;
        $items = $data['items'] ?? [];

        // Validate
        if (empty($storeName)) {
            $this->json(['success' => false, 'error' => 'Podaj nazwę sklepu'], 400);
            return;
        }

        if ($totalAmount <= 0 && empty($items)) {
            $this->json(['success' => false, 'error' => 'Podaj kwotę lub produkty'], 400);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy format daty'], 400);
            return;
        }

        try {
            if (!empty($items)) {
                $totalAmount = 0;
                foreach ($items as $item) {
                    $price = (float) ($item['price'] ?? 0);
                    $quantity = (int) ($item['quantity'] ?? 1);
                    $totalAmount += $price * $quantity;
                }
            }

            // Create receipt
            $receiptId = $this->receiptRepository->createReceipt(
                $userId,
                $storeName,
                $date,
                $totalAmount,
                $imagePath,
                $notes ?: null
            );

            // Add items
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (!empty($item['name'])) {
                        $this->receiptRepository->addReceiptItem(
                            $receiptId,
                            $this->sanitize($item['name']),
                            (float) ($item['price'] ?? 0),
                            !empty($item['category_id']) ? (int) $item['category_id'] : null,
                            (int) ($item['quantity'] ?? 1)
                        );
                    }
                }
            }

            $this->json([
                'success' => true,
                'data' => [
                    'receipt_id' => $receiptId
                ]
            ]);
        } catch (Exception $e) {
            error_log("Save receipt error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Błąd podczas zapisywania'], 500);
        }
    }

    /**
     * Get recent receipts
     */
    public function recentReceipts(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $limit = isset($_GET['limit']) ? min(20, max(1, (int) $_GET['limit'])) : 5;

        $receipts = $this->receiptRepository->getRecentReceipts($userId, $limit);

        $this->json([
            'success' => true,
            'data' => $receipts
        ]);
    }

    /**
     * Get calendar data (expenses by day for a month)
     */
    public function calendarData(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowa data'], 400);
            return;
        }

        $dailyExpenses = $this->receiptRepository->getMonthlyExpensesByDay($userId, $month, $year);

        $this->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'expenses' => $dailyExpenses
            ]
        ]);
    }
}