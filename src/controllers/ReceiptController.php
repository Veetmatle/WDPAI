<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';
require_once __DIR__ . '/../repository/CategoryRepository.php';

/**
 * Receipt Controller
 * Handles receipt detail view
 */
class ReceiptController extends AppController
{
    private ReceiptRepository $receiptRepository;
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
        $this->categoryRepository = CategoryRepository::getInstance();
    }

    /**
     * Show receipt details
     */
    public function show(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $receiptId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($receiptId <= 0) {
            header('Location: /dashboard');
            exit;
        }

        $receipt = $this->receiptRepository->getReceiptById($receiptId, $userId);
        
        if (!$receipt) {
            $_SESSION['error'] = 'Paragon nie został znaleziony';
            header('Location: /dashboard');
            exit;
        }

        // Pobierz produkty z paragonu
        $items = $this->receiptRepository->getReceiptItems($receiptId);

        $this->render('receipt-details', [
            'receipt' => $receipt,
            'items' => $items,
            'activePage' => ''
        ]);
    }

    /**
     * Delete receipt
     */
    public function delete(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiptId = isset($_POST['receipt_id']) ? (int)$_POST['receipt_id'] : 0;
            $csrfToken = $_POST['csrf_token'] ?? '';
            $returnUrl = $_POST['return_url'] ?? '/dashboard';

            // Sanitize return URL
            $returnUrl = $this->sanitizeReturnUrl($returnUrl);

            if (!$this->validateCSRF($csrfToken)) {
                $_SESSION['error'] = 'Nieprawidłowy token bezpieczeństwa. Odśwież stronę i spróbuj ponownie.';
                header('Location: ' . $returnUrl);
                exit;
            }

            if ($receiptId <= 0) {
                $_SESSION['error'] = 'Nieprawidłowy identyfikator paragonu';
                header('Location: ' . $returnUrl);
                exit;
            }

            // Pobierz paragon przed usunięciem
            $receipt = $this->receiptRepository->getReceiptById($receiptId, $userId);

            if (!$receipt) {
                $_SESSION['error'] = 'Paragon nie istnieje lub nie masz do niego dostępu';
                header('Location: ' . $returnUrl);
                exit;
            }

            try {
                // Usuń plik obrazka jeśli istnieje
                if (!empty($receipt['receipt_image_path'])) {
                    $imagePath = __DIR__ . '/../../public' . $receipt['receipt_image_path'];
                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }

                // Usuń paragon z bazy
                $deleted = $this->receiptRepository->deleteReceipt($receiptId, $userId);

                if ($deleted) {
                    $_SESSION['success'] = 'Paragon został pomyślnie usunięty';
                } else {
                    $_SESSION['error'] = 'Nie udało się usunąć paragonu';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Wystąpił błąd podczas usuwania paragonu';
                error_log('Receipt delete error: ' . $e->getMessage());
            }

            header('Location: ' . $returnUrl);
            exit;
        }

        // Dla GET - przekieruj na dashboard
        header('Location: /dashboard');
        exit;
    }

    /**
     * Sanitize return URL to prevent open redirect
     */
    private function sanitizeReturnUrl(string $url): string
    {
        // Only allow relative URLs starting with /
        if (preg_match('#^/[a-zA-Z0-9\-_/]*(\?[a-zA-Z0-9\-_=&]*)?$#', $url)) {
            return $url;
        }
        return '/dashboard';
    }

    /**
     * Edit receipt
     */
    public function edit(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $receiptId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($receiptId <= 0) {
            header('Location: /dashboard');
            exit;
        }

        $receipt = $this->receiptRepository->getReceiptById($receiptId, $userId);
        
        if (!$receipt) {
            $_SESSION['error'] = 'Paragon nie został znaleziony';
            header('Location: /dashboard');
            exit;
        }

        $items = $this->receiptRepository->getReceiptItems($receiptId);
        $categories = $this->categoryRepository->getCategoriesByUserId($userId);

        $this->render('edit-receipt', [
            'receipt' => $receipt,
            'items' => $items,
            'categories' => $categories,
            'activePage' => ''
        ]);
    }

    /**
     * Update receipt (API endpoint)
     */
    public function updateApi(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowa metoda'], 405);
            return;
        }

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $receiptId = isset($_POST['receipt_id']) ? (int)$_POST['receipt_id'] : 0;
        $storeName = $this->sanitize($_POST['store_name'] ?? '');
        $date = $_POST['receipt_date'] ?? date('Y-m-d');
        $totalAmount = (float) ($_POST['total_amount'] ?? 0);
        $notes = $this->sanitize($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];

        if ($receiptId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy identyfikator paragonu'], 400);
            return;
        }

        // Sprawdź czy paragon należy do użytkownika
        $receipt = $this->receiptRepository->getReceiptById($receiptId, $userId);
        if (!$receipt) {
            $this->json(['success' => false, 'error' => 'Paragon nie został znaleziony'], 404);
            return;
        }

        if (empty($storeName)) {
            $this->json(['success' => false, 'error' => 'Podaj nazwę sklepu'], 400);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy format daty'], 400);
            return;
        }

        try {
            // Oblicz sumę z produktów
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

            // Aktualizuj paragon
            $this->receiptRepository->updateReceipt($receiptId, $userId, $storeName, $date, $totalAmount, $notes ?: null);

            // Usuń stare produkty i dodaj nowe
            $this->receiptRepository->deleteReceiptItems($receiptId);

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
            error_log("Error updating receipt: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Błąd podczas zapisywania'], 500);
        }
    }
}