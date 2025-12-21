<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/BudgetRepository.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';

/**
 * Budget Controller
 * Handles settings, budget planning, and user profile
 */
class BudgetController extends AppController
{
    private UserRepository $userRepository;
    private BudgetRepository $budgetRepository;
    private ReceiptRepository $receiptRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
        $this->budgetRepository = BudgetRepository::getInstance();
        $this->receiptRepository = ReceiptRepository::getInstance();
    }

    /**
     * Settings page
     */
    public function settings(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        $user = $this->userRepository->getUserById($userId);

        // Get current budget
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $currentBudget = $this->budgetRepository->getBudget($userId, $currentMonth, $currentYear);
        $monthlyTotal = $this->receiptRepository->getMonthlyTotal($userId, $currentMonth, $currentYear);
        $budgetStatus = $this->budgetRepository->getBudgetStatus($userId, $currentMonth, $currentYear, $monthlyTotal);

        $this->render('settings', [
            'user' => $user ? $user->toArray() : [],
            'budget' => $budgetStatus,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'activePage' => 'settings'
        ]);
    }

    /**
     * Budget management
     */
    public function budget(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();

        if ($this->isPost()) {
            $this->handleBudgetUpdate($userId);
            return;
        }

        // Get all budgets
        $budgets = $this->budgetRepository->getAllBudgets($userId);

        // Get current month data
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $monthlyTotal = $this->receiptRepository->getMonthlyTotal($userId, $currentMonth, $currentYear);
        $budgetStatus = $this->budgetRepository->getBudgetStatus($userId, $currentMonth, $currentYear, $monthlyTotal);

        $monthNames = [
            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
        ];

        $this->render('budget', [
            'budgets' => $budgets,
            'budgetStatus' => $budgetStatus,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'monthNames' => $monthNames,
            'activePage' => 'settings'
        ]);
    }

    /**
     * Handle budget update
     */
    private function handleBudgetUpdate(int $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowe żądanie'));
            return;
        }

        $month = isset($_POST['month']) ? (int) $_POST['month'] : (int) date('n');
        $year = isset($_POST['year']) ? (int) $_POST['year'] : (int) date('Y');
        $amountLimit = isset($_POST['amount_limit']) ? (float) $_POST['amount_limit'] : 0;

        // Validate
        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowa data'));
            return;
        }

        if ($amountLimit < 0 || $amountLimit > 9999999.99) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowa kwota'));
            return;
        }

        try {
            $result = $this->budgetRepository->setBudget($userId, $month, $year, $amountLimit);
            
            if ($result) {
                $this->redirect('/settings?success=' . urlencode('Budżet został zapisany'));
            } else {
                $this->redirect('/settings?error=' . urlencode('Błąd podczas zapisywania'));
            }
        } catch (Exception $e) {
            error_log("Budget update error: " . $e->getMessage());
            $this->redirect('/settings?error=' . urlencode('Błąd serwera'));
        }
    }

    /**
     * User profile
     */
    public function profile(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();

        if ($this->isPost()) {
            // Check which action
            $action = $_POST['action'] ?? 'update_profile';
            
            if ($action === 'update_password' || isset($_POST['current_password'])) {
                $this->handlePasswordUpdate($userId);
            } else {
                $this->handleProfileUpdate($userId);
            }
            return;
        }

        $user = $this->userRepository->getUserById($userId);

        $this->render('profile', [
            'userProfile' => $user ? $user->toArray() : null,
            'activePage' => 'settings'
        ]);
    }

    /**
     * Handle profile update
     */
    private function handleProfileUpdate(int $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowe żądanie'));
            return;
        }

        $name = $this->sanitize($_POST['name'] ?? '');
        $surname = $this->sanitize($_POST['surname'] ?? '');

        // Validate
        if (empty($name) || empty($surname)) {
            $this->redirect('/settings?error=' . urlencode('Wypełnij wszystkie pola'));
            return;
        }

        if (strlen($name) > 100 || strlen($surname) > 100) {
            $this->redirect('/settings?error=' . urlencode('Dane są za długie'));
            return;
        }

        if (!preg_match('/^[\p{L}\s\-]{2,}$/u', $name) || !preg_match('/^[\p{L}\s\-]{2,}$/u', $surname)) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowe znaki w imieniu lub nazwisku'));
            return;
        }

        try {
            $result = $this->userRepository->updateUser($userId, $name, $surname);
            
            if ($result) {
                // Update session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_surname'] = $surname;
                
                $this->redirect('/settings?success=' . urlencode('Profil został zaktualizowany'));
            } else {
                $this->redirect('/settings?error=' . urlencode('Błąd podczas zapisywania'));
            }
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->redirect('/settings?error=' . urlencode('Błąd serwera'));
        }
    }

    /**
     * Handle password update
     */
    private function handlePasswordUpdate(int $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowe żądanie'));
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Get user
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            $this->redirect('/settings?error=' . urlencode('Użytkownik nie znaleziony'));
            return;
        }

        // Verify current password
        if (!$user->verifyPassword($currentPassword)) {
            $this->redirect('/settings?error=' . urlencode('Nieprawidłowe obecne hasło'));
            return;
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            $this->redirect('/settings?error=' . urlencode('Nowe hasło musi mieć minimum 8 znaków'));
            return;
        }

        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $this->redirect('/settings?error=' . urlencode('Hasło musi zawierać wielką literę, małą literę i cyfrę'));
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirect('/settings?error=' . urlencode('Nowe hasła nie są identyczne'));
            return;
        }

        try {
            $result = $this->userRepository->updatePassword($userId, $newPassword);
            
            if ($result) {
                $this->redirect('/settings?success=' . urlencode('Hasło zostało zmienione'));
            } else {
                $this->redirect('/settings?error=' . urlencode('Błąd podczas zmiany hasła'));
            }
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            $this->redirect('/settings?error=' . urlencode('Błąd serwera'));
        }
    }
}