<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';
require_once __DIR__ . '/../repository/BudgetRepository.php';

/**
 * Stats Controller
 * Handles statistics and charts
 */
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

    /**
     * Display statistics page
     */
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();

        // Pobierz miesiąc i rok z parametrów lub użyj bieżących
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        // Walidacja
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        // Pobierz wydatki pogrupowane po kategoriach
        $categoryExpenses = $this->receiptRepository->getExpensesByCategory($userId, $month, $year);

        // Oblicz sumę
        $totalExpenses = 0;
        foreach ($categoryExpenses as $cat) {
            $totalExpenses += (float) $cat['total'];
        }

        // Pobierz budżet
        $budget = $this->budgetRepository->getBudget($userId, $month, $year);

        // Pobierz podsumowanie ostatnich 6 miesięcy
        $monthlySummary = $this->receiptRepository->getExpensesSummary($userId, 6);

        // Poprzedni i następny miesiąc
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
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'activePage' => 'stats'
        ]);
    }
}