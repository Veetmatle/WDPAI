<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';
require_once __DIR__ . '/../repository/BudgetRepository.php';

class StatsController extends AppController
{
    private ReceiptRepository $receiptRepository;
    private BudgetRepository $budgetRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
        $this->budgetRepository = BudgetRepository::getInstance();
    }

    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();

        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $categoryExpenses = $this->receiptRepository->getExpensesByCategory($userId, $month, $year);

        $totalExpenses = 0;
        foreach ($categoryExpenses as $cat) {
            $totalExpenses += (float) $cat['total'];
        }

        $budget = $this->budgetRepository->getBudget($userId, $month, $year);

        $monthlySummary = $this->receiptRepository->getExpensesSummary($userId, 6);

        $monthlyReceipts = $this->receiptRepository->getReceiptsByMonth($userId, $month, $year, 3);

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $monthNames = [
            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
        ];

        $this->render('stats', [
            'month' => $month,
            'year' => $year,
            'monthName' => $monthNames[$month],
            'categoryExpenses' => $categoryExpenses,
            'totalExpenses' => $totalExpenses,
            'budget' => $budget,
            'monthlySummary' => $monthlySummary,
            'monthlyReceipts' => $monthlyReceipts,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'activePage' => 'stats'
        ]);
    }
}