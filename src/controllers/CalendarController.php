<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';

/**
 * Calendar Controller
 * Handles the calendar view for expense tracking
 */
class CalendarController extends AppController
{
    private ReceiptRepository $receiptRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
    }

    /**
     * Display calendar
     */
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        
        // Get month and year from query params or use current
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        // Validate month and year
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        // Get expenses grouped by day
        $dailyExpenses = $this->receiptRepository->getMonthlyExpensesByDay($userId, $month, $year);

        // Build calendar data
        $calendarData = $this->buildCalendarData($month, $year, $dailyExpenses);

        // Calculate previous and next month
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

        $this->render('calendar', [
            'month' => $month,
            'year' => $year,
            'monthName' => $monthNames[$month],
            'calendarData' => $calendarData,
            'dailyExpenses' => $dailyExpenses,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'activePage' => 'calendar'
        ]);
    }

    /**
     * Build calendar grid data
     */
    private function buildCalendarData(int $month, int $year, array $dailyExpenses): array
    {
        $calendar = [];
        
        // First day of month (1 = Monday, 7 = Sunday in our grid)
        $firstDayOfMonth = date('N', mktime(0, 0, 0, $month, 1, $year));
        $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        
        // Previous month
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));

        $today = date('Y-m-d');
        $currentMonthYear = sprintf('%04d-%02d', $year, $month);

        // Days from previous month
        for ($i = $firstDayOfMonth - 1; $i > 0; $i--) {
            $day = $daysInPrevMonth - $i + 1;
            $dateString = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $day);
            $calendar[] = [
                'day' => $day,
                'date' => $dateString,
                'is_current_month' => false,
                'is_today' => ($dateString === $today),
                'expense' => null
            ];
        }

        // Days of current month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $expense = $dailyExpenses[$dateString] ?? null;
            
            $calendar[] = [
                'day' => $day,
                'date' => $dateString,
                'is_current_month' => true,
                'is_today' => ($dateString === $today),
                'expense' => $expense
            ];
        }

        // Days from next month
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $remainingDays = 42 - count($calendar); // 6 rows * 7 days
        for ($day = 1; $day <= $remainingDays; $day++) {
            $dateString = sprintf('%04d-%02d-%02d', $nextYear, $nextMonth, $day);
            $calendar[] = [
                'day' => $day,
                'date' => $dateString,
                'is_current_month' => false,
                'is_today' => ($dateString === $today),
                'expense' => null
            ];
        }

        return $calendar;
    }
}