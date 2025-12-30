<?php
$pageTitle = 'Kalendarz';
$activePage = 'calendar';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Kalendarz - ChronoCash</title>
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/calendar.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="calendar-wrapper">
        <!-- Header -->
        <header class="calendar-header">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="calendar-nav-btn">
                <span class="material-symbols-outlined">arrow_back_ios_new</span>
            </a>
            <h2 class="calendar-month-title">
                <?= htmlspecialchars($monthName ?? '') ?> <?= $year ?>
            </h2>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="calendar-nav-btn">
                <span class="material-symbols-outlined">arrow_forward_ios</span>
            </a>
        </header>

        <main class="calendar-main">
            <!-- Weekday Headers -->
            <div class="calendar-weekdays">
                <?php 
                $weekdays = ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So', 'Nd'];
                foreach ($weekdays as $day): 
                ?>
                <p class="calendar-weekday"><?= $day ?></p>
                <?php endforeach; ?>
            </div>

            <!-- Calendar Grid -->
            <div class="calendar-grid">
                <?php foreach ($calendarData ?? [] as $dayData): 
                    $dateStr = $dayData['date'];
                    $hasExpenses = isset($dayData['expense']) && $dayData['expense'] > 0;
                    $isToday = $dayData['is_today'];
                    $isCurrentMonth = $dayData['is_current_month'];
                    
                    if ($hasExpenses) {
                        $link = "/daily-expenses?date=" . $dateStr;
                    } else {
                        $link = "/add-expense?date=" . $dateStr;
                    }
                ?>
                <a href="<?= $link ?>" class="calendar-day <?= $isCurrentMonth ? '' : 'other-month' ?>">
                    <div class="calendar-day-content">
                        <?php if ($isToday): ?>
                        <div class="calendar-day-today">
                            <span><?= $dayData['day'] ?></span>
                        </div>
                        <?php else: ?>
                        <span class="calendar-day-number"><?= $dayData['day'] ?></span>
                        <?php endif; ?>
                        
                        <?php if ($hasExpenses): ?>
                        <span class="calendar-day-expense">
                            -<?= number_format($dayData['expense'], 2, ',', ' ') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
</body>
</html>