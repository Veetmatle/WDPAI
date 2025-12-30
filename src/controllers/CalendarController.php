<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ReceiptRepository.php';

class CalendarController extends AppController
{
    private ReceiptRepository $receiptRepository;

    public function __construct()
    {
        parent::__construct();
        $this->receiptRepository = ReceiptRepository::getInstance();
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

        $dailyExpenses = $this->receiptRepository->getMonthlyExpensesByDay($userId, $month, $year);

        $calendarData = $this->buildCalendarData($month, $year, $dailyExpenses);

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

    private function buildCalendarData(int $month, int $year, array $dailyExpenses): array
    {
        $calendar = [];
        
        $firstDayOfMonth = date('N', mktime(0, 0, 0, $month, 1, $year));
        $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));

        $today = date('Y-m-d');
        $currentMonthYear = sprintf('%04d-%02d', $year, $month);

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

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $remainingDays = 42 - count($calendar);
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