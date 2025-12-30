<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';
require_once __DIR__ . '/../repository/BudgetRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController
{
    private ReceiptRepository $receiptRepository;
    private BudgetRepository $budgetRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
        $this->budgetRepository = BudgetRepository::getInstance();
        $this->userRepository = UserRepository::getInstance();
    }

    public function index(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        $userObj = $this->userRepository->getUserById($userId);
        $user = $userObj ? $userObj->toArray() : [
            'name' => $_SESSION['user_name'] ?? 'Użytkownik',
            'surname' => $_SESSION['user_surname'] ?? '',
            'email' => $_SESSION['user_email'] ?? ''
        ];

        $monthlyTotal = $this->receiptRepository->getMonthlyTotal($userId, $currentMonth, $currentYear);

        $budget = $this->budgetRepository->getBudget($userId, $currentMonth, $currentYear);

        $recentReceipts = $this->receiptRepository->getRecentReceipts($userId, 3);

        $expensesSummary = $this->receiptRepository->getExpensesSummary($userId, 4);

        $this->render('dashboard', [
            'user' => $user,
            'monthlyTotal' => $monthlyTotal,
            'budget' => $budget,
            'recentReceipts' => $recentReceipts,
            'expensesSummary' => $expensesSummary,
            'activePage' => 'dashboard'
        ]);
    }
}