<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';
require_once __DIR__ . '/../repository/CategoryRepository.php';
require_once __DIR__ . '/../repository/BudgetRepository.php';

/**
 * Expense Controller
 * Handles expense management: view daily expenses, add new expense
 */
class ExpenseController extends AppController
{
    private ReceiptRepository $receiptRepository;
    private CategoryRepository $categoryRepository;
    private BudgetRepository $budgetRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
        $this->categoryRepository = CategoryRepository::getInstance();
        $this->budgetRepository = BudgetRepository::getInstance();
    }

    /**
     * Display daily expenses
     */
    public function daily(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        
        // Get date from query params or use today
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        // Get receipts for this date
        $receipts = $this->receiptRepository->getReceiptsByDate($userId, $date);
        $dailyTotal = $this->receiptRepository->getDailyTotal($userId, $date);

        // Format date for display
        $timestamp = strtotime($date);
        $dayNames = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
        $monthNames = [
            1 => 'stycznia', 2 => 'lutego', 3 => 'marca', 4 => 'kwietnia',
            5 => 'maja', 6 => 'czerwca', 7 => 'lipca', 8 => 'sierpnia',
            9 => 'września', 10 => 'października', 11 => 'listopada', 12 => 'grudnia'
        ];
        
        $formattedDate = $dayNames[date('w', $timestamp)] . ', ' . 
                         date('d', $timestamp) . ' ' . 
                         $monthNames[(int)date('n', $timestamp)] . ' ' . 
                         date('Y', $timestamp);

        $this->render('daily-expenses', [
            'date' => $date,
            'formattedDate' => $formattedDate,
            'receipts' => $receipts,
            'dailyTotal' => $dailyTotal
        ]);
    }

    /**
     * Add new expense form
     */
    public function add(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        
        // Get date from query params or use today
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        // Handle form submission
        if ($this->isPost()) {
            $this->handleAddExpenseForm($userId);
            return;
        }

        // Get categories for the form
        $categories = $this->categoryRepository->getCategoriesByUserId($userId);

        // Get budget status for current month
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $monthlyTotal = $this->receiptRepository->getMonthlyTotal($userId, $currentMonth, $currentYear);
        $budget = $this->budgetRepository->getBudgetStatus($userId, $currentMonth, $currentYear, $monthlyTotal);

        $this->render('add-expense', [
            'date' => $date,
            'categories' => $categories,
            'budget' => $budget
        ]);
    }

    /**
     * Handle expense form submission (non-API - redirect)
     */
    private function handleAddExpenseForm(int $userId): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            $_SESSION['error'] = 'Nieprawidłowe żądanie (CSRF)';
            header('Location: /add-expense');
            exit;
        }

        $storeName = $this->sanitize($_POST['store_name'] ?? '');
        $date = $_POST['receipt_date'] ?? $_POST['date'] ?? date('Y-m-d');
        $totalAmount = (float) ($_POST['total_amount'] ?? 0);
        $notes = $this->sanitize($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];

        // Validate required fields
        if (empty($storeName)) {
            $_SESSION['error'] = 'Podaj nazwę sklepu';
            header('Location: /add-expense');
            exit;
        }

        if ($totalAmount <= 0 && empty($items)) {
            $_SESSION['error'] = 'Podaj kwotę lub dodaj produkty';
            header('Location: /add-expense');
            exit;
        }

        // Validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['error'] = 'Nieprawidłowy format daty';
            header('Location: /add-expense');
            exit;
        }

        try {
            // Handle file upload if present
            $imagePath = null;
            if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleFileUpload($_FILES['receipt_image']);
            }

            // Calculate total from items if items provided
            if (!empty($items) && is_array($items)) {
                $calculatedTotal = 0;
                foreach ($items as $item) {
                    if (!empty($item['name']) && isset($item['price'])) {
                        $price = (float) $item['price'];
                        $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;
                        $calculatedTotal += $price * $quantity;
                    }
                }
                if ($calculatedTotal > 0 && $totalAmount <= 0) {
                    $totalAmount = $calculatedTotal;
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

            // Add items if provided
            if (!empty($items) && is_array($items)) {
                foreach ($items as $item) {
                    if (!empty($item['name']) && isset($item['price'])) {
                        $this->receiptRepository->addReceiptItem(
                            $receiptId,
                            $this->sanitize($item['name']),
                            (float) $item['price'],
                            !empty($item['category_id']) ? (int) $item['category_id'] : null,
                            isset($item['quantity']) ? (int) $item['quantity'] : 1
                        );
                    }
                }
            }

            $_SESSION['success'] = 'Wydatek został dodany pomyślnie';
            header('Location: /receipt?id=' . $receiptId);
            exit;
        } catch (Exception $e) {
            error_log("Error adding expense: " . $e->getMessage());
            $_SESSION['error'] = 'Błąd podczas zapisywania: ' . $e->getMessage();
            header('Location: /add-expense');
            exit;
        }
    }

    /**
     * Handle expense form submission (API endpoint)
     */
    public function addApi(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $this->handleAddExpense($userId);
    }

    /**
     * Handle expense form submission
     */
    private function handleAddExpense(int $userId): void
    {
        // Validate CSRF
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $storeName = $this->sanitize($_POST['store_name'] ?? '');
        // Obsługa obu nazw pola daty
        $date = $_POST['receipt_date'] ?? $_POST['date'] ?? date('Y-m-d');
        $totalAmount = (float) ($_POST['total_amount'] ?? 0);
        $notes = $this->sanitize($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];

        // Validate required fields
        if (empty($storeName)) {
            $this->json(['success' => false, 'error' => 'Podaj nazwę sklepu'], 400);
            return;
        }

        if ($totalAmount <= 0 && empty($items)) {
            $this->json(['success' => false, 'error' => 'Podaj kwotę lub produkty'], 400);
            return;
        }

        // Validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy format daty'], 400);
            return;
        }

        // Limit input lengths
        if (strlen($storeName) > 255 || strlen($notes) > 1000) {
            $this->json(['success' => false, 'error' => 'Dane wejściowe są za długie'], 400);
            return;
        }

        try {
            // Handle file upload if present
            $imagePath = null;
            if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleFileUpload($_FILES['receipt_image']);
            }

            // Calculate total from items if items provided and total is 0
            if (!empty($items) && is_array($items)) {
                $calculatedTotal = 0;
                foreach ($items as $item) {
                    if (!empty($item['name']) && isset($item['price'])) {
                        $price = (float) $item['price'];
                        $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;
                        $calculatedTotal += $price * $quantity;
                    }
                }
                if ($calculatedTotal > 0) {
                    $totalAmount = $calculatedTotal;
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

            // Add items if provided
            if (!empty($items) && is_array($items)) {
                foreach ($items as $item) {
                    if (!empty($item['name']) && isset($item['price'])) {
                        $this->receiptRepository->addReceiptItem(
                            $receiptId,
                            $this->sanitize($item['name']),
                            (float) $item['price'],
                            !empty($item['category_id']) ? (int) $item['category_id'] : null,
                            isset($item['quantity']) ? (int) $item['quantity'] : 1
                        );
                    }
                }
            }

            $this->json(['success' => true, 'receipt_id' => $receiptId]);
        } catch (Exception $e) {
            error_log("Error adding expense: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Błąd podczas zapisywania'], 500);
        }
    }

    /**
     * Handle file upload
     */
    private function handleFileUpload(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        // Validate file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Nieprawidłowy typ pliku');
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            throw new Exception('Plik jest za duży (max 10MB)');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('receipt_', true) . '.' . $extension;
        $uploadDir = __DIR__ . '/../../public/uploads/';
        $uploadPath = $uploadDir . $filename;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Nie można zapisać pliku');
        }

        return '/uploads/' . $filename;
    }

    /**
     * Display all expenses (optionally filtered by month)
     */
    public function all(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();

        // Sprawdź czy filtrujemy po miesiącu
        $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        
        $monthNames = [
            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
        ];
        
        $pageTitle = 'Wszystkie wydatki';
        $filterMonth = null;
        $filterYear = null;
        
        if ($month && $year && $month >= 1 && $month <= 12 && $year >= 2000) {
            // Pobierz paragony z danego miesiąca
            $receipts = $this->receiptRepository->getReceiptsByMonth($userId, $month, $year, 100);
            $pageTitle = 'Wydatki: ' . $monthNames[$month] . ' ' . $year;
            $filterMonth = $month;
            $filterYear = $year;
        } else {
            // Pobierz wszystkie paragony
            $receipts = $this->receiptRepository->getReceiptsByUserId($userId, 100);
        }

        // Grupuj po dacie
        $groupedReceipts = [];
        foreach ($receipts as $receipt) {
            $date = $receipt['receipt_date'];
            if (!isset($groupedReceipts[$date])) {
                $groupedReceipts[$date] = [
                    'date' => $date,
                    'receipts' => [],
                    'total' => 0
                ];
            }
            $groupedReceipts[$date]['receipts'][] = $receipt;
            $groupedReceipts[$date]['total'] += (float)$receipt['total_amount'];
        }

        $this->render('expenses', [
            'groupedReceipts' => $groupedReceipts,
            'pageTitle' => $pageTitle,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'activePage' => 'expenses'
        ]);
    }
}