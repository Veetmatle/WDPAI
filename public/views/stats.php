<?php
/** @var int $month */
/** @var int $year */
/** @var string $monthName */
/** @var array $categoryExpenses */
/** @var float $totalExpenses */
/** @var array|null $budget */
/** @var array $monthlySummary */
/** @var array $monthlyReceipts */
/** @var int $prevMonth */
/** @var int $prevYear */
/** @var int $nextMonth */
/** @var int $nextYear */

$pageTitle = 'Statystyki';
$activePage = 'stats';

// Znajdź maksymalną wartość dla skalowania wykresu kategorii
$maxCategoryValue = 0;
foreach ($categoryExpenses as $cat) {
    if ((float)$cat['total'] > $maxCategoryValue) {
        $maxCategoryValue = (float)$cat['total'];
    }
}
if ($maxCategoryValue == 0) $maxCategoryValue = 100;

// Przygotuj dane dla wykresu miesięcznego
$monthNames = ['', 'Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'];
$chartData = [];
$summaryReversed = array_reverse($monthlySummary ?? []);
foreach ($summaryReversed as $data) {
    $chartData[] = [
        'label' => $monthNames[(int)$data['month']] ?? '',
        'value' => (float)($data['total'] ?? 0),
        'month' => (int)$data['month'],
        'year' => (int)$data['year']
    ];
}

$maxMonthlyValue = 0;
foreach ($chartData as $d) {
    if ($d['value'] > $maxMonthlyValue) {
        $maxMonthlyValue = $d['value'];
    }
}
if ($maxMonthlyValue == 0) $maxMonthlyValue = 1000;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($pageTitle) ?> - ChronoCash</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/stats.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="stats-wrapper">
        <!-- Header -->
        <div class="stats-header">
            <h1 class="stats-title">Statystyki</h1>
        </div>

        <!-- Month Selector -->
        <div class="stats-month-selector">
            <a href="/stats?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="stats-nav-btn">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
            <div class="stats-month-label">
                <h2><?= htmlspecialchars($monthName) ?> <?= $year ?></h2>
            </div>
            <?php if ($month == (int)date('n') && $year == (int)date('Y')): ?>
            <div class="stats-nav-placeholder"></div>
            <?php else: ?>
            <a href="/stats?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="stats-nav-btn">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Summary Card -->
        <div class="stats-summary-card">
            <p class="stats-summary-label">Wydatki w <?= htmlspecialchars($monthName) ?></p>
            <p class="stats-summary-amount">
                <?= number_format($totalExpenses, 2, ',', ' ') ?> <span class="stats-summary-currency">zł</span>
            </p>
            
            <?php if (!empty($budget) && isset($budget['amount_limit']) && $budget['amount_limit'] > 0): ?>
            <?php 
                $limit = (float)$budget['amount_limit'];
                $remaining = $limit - $totalExpenses;
                $percentage = min(100, ($totalExpenses / $limit) * 100);
                $barClass = $percentage > 100 ? 'danger' : ($percentage > 90 ? 'warning' : '');
            ?>
            <div class="stats-budget-section">
                <div class="stats-budget-info">
                    <span>Budżet: <?= number_format($limit, 2, ',', ' ') ?> zł</span>
                    <span class="<?= $remaining < 0 ? 'text-danger' : '' ?>">
                        <?= $remaining >= 0 ? 'Zostało' : 'Przekroczono o' ?>: <?= number_format(abs($remaining), 2, ',', ' ') ?> zł
                    </span>
                </div>
                <div class="stats-budget-bar">
                    <div class="stats-budget-progress <?= $barClass ?>" style="width: <?= min(100, $percentage) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Category Breakdown -->
        <div class="stats-card">
            <h2 class="stats-card-title">
                <span class="material-symbols-outlined">pie_chart</span>
                Wydatki według kategorii
            </h2>
            
            <?php if (!empty($categoryExpenses)): ?>
            <div class="stats-categories">
                <?php foreach ($categoryExpenses as $cat): ?>
                <?php 
                    $catTotal = (float)$cat['total'];
                    $percentage = $totalExpenses > 0 ? ($catTotal / $totalExpenses) * 100 : 0;
                    $barWidth = $maxCategoryValue > 0 ? ($catTotal / $maxCategoryValue) * 100 : 0;
                ?>
                <div class="stats-category-item">
                    <div class="stats-category-header">
                        <div class="stats-category-info">
                            <div class="stats-category-icon" style="background-color: <?= htmlspecialchars($cat['color_hex']) ?>20">
                                <span class="material-symbols-outlined" style="color: <?= htmlspecialchars($cat['color_hex']) ?>">
                                    <?= htmlspecialchars($cat['icon_name']) ?>
                                </span>
                            </div>
                            <span class="stats-category-name"><?= htmlspecialchars($cat['category_name']) ?></span>
                        </div>
                        <div class="stats-category-amount">
                            <span class="stats-category-value"><?= number_format($catTotal, 2, ',', ' ') ?> zł</span>
                            <span class="stats-category-percent">(<?= number_format($percentage, 1) ?>%)</span>
                        </div>
                    </div>
                    <div class="stats-category-bar">
                        <div class="stats-category-progress" style="width: <?= $barWidth ?>%; background-color: <?= htmlspecialchars($cat['color_hex']) ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="stats-empty">
                <span class="material-symbols-outlined">donut_large</span>
                <p>Brak wydatków w tym miesiącu</p>
                <a href="/add-expense" class="btn btn-primary">Dodaj wydatek</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Monthly Comparison Chart -->
        <div class="stats-card">
            <h2 class="stats-card-title">
                <span class="material-symbols-outlined">bar_chart</span>
                Porównanie miesięczne
            </h2>
            
            <div class="stats-chart-container">
                <?php foreach ($chartData as $index => $data): ?>
                <?php 
                    $heightPercent = $maxMonthlyValue > 0 ? ($data['value'] / $maxMonthlyValue) * 100 : 0;
                    $heightPercent = max(5, $heightPercent);
                    $heightPx = max(10, round(150 * $heightPercent / 100));
                    $isCurrentSelection = ($data['month'] === $month && $data['year'] === $year);
                ?>
                <a href="/stats?month=<?= $data['month'] ?>&year=<?= $data['year'] ?>" class="stats-chart-bar-wrapper">
                    <span class="stats-chart-value">
                        <?= $data['value'] > 0 ? number_format($data['value'], 0, ',', ' ') : '-' ?>
                    </span>
                    <div class="stats-chart-bar <?= $isCurrentSelection ? 'current' : '' ?>" style="height: <?= $heightPx ?>px;"></div>
                    <span class="stats-chart-label"><?= htmlspecialchars($data['label']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary -->
        <?php if (!empty($categoryExpenses)): ?>
        <div class="stats-card">
            <h2 class="stats-card-title">
                <span class="material-symbols-outlined">insights</span>
                Podsumowanie
            </h2>
            
            <div class="stats-summary-grid">
                <div class="stats-summary-item">
                    <p class="stats-summary-item-label">Kategorie</p>
                    <p class="stats-summary-item-value"><?= count($categoryExpenses) ?></p>
                </div>
                <div class="stats-summary-item">
                    <p class="stats-summary-item-label">Średnio/dzień</p>
                    <p class="stats-summary-item-value">
                        <?php 
                            $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
                            $currentDay = ($month == (int)date('n') && $year == (int)date('Y')) ? (int)date('j') : $daysInMonth;
                            $avgPerDay = $currentDay > 0 ? $totalExpenses / $currentDay : 0;
                            echo number_format($avgPerDay, 0, ',', ' ');
                        ?> <span class="stats-summary-item-unit">zł</span>
                    </p>
                </div>
            </div>
            
            <?php if (!empty($categoryExpenses)): ?>
            <div class="stats-largest-expense">
                <p class="stats-largest-label">Największy wydatek</p>
                <div class="stats-largest-content">
                    <div class="stats-largest-icon" style="background-color: <?= htmlspecialchars($categoryExpenses[0]['color_hex']) ?>20">
                        <span class="material-symbols-outlined" style="color: <?= htmlspecialchars($categoryExpenses[0]['color_hex']) ?>">
                            <?= htmlspecialchars($categoryExpenses[0]['icon_name']) ?>
                        </span>
                    </div>
                    <div>
                        <p class="stats-largest-name"><?= htmlspecialchars($categoryExpenses[0]['category_name']) ?></p>
                        <p class="stats-largest-amount"><?= number_format((float)$categoryExpenses[0]['total'], 2, ',', ' ') ?> zł</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Monthly Receipts -->
        <div class="stats-card">
            <div class="stats-card-header">
                <h2 class="stats-card-title">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Paragony z <?= htmlspecialchars($monthName) ?>
                </h2>
                <a href="/expenses?month=<?= $month ?>&year=<?= $year ?>" class="stats-card-link">Zobacz wszystkie</a>
            </div>
            
            <?php if (!empty($monthlyReceipts)): ?>
            <ul class="stats-receipts-list">
                <?php foreach ($monthlyReceipts as $receipt): ?>
                <li>
                    <a href="/receipt?id=<?= $receipt['id'] ?>" class="stats-receipt-item">
                        <div class="stats-receipt-icon">
                            <span class="material-symbols-outlined">
                                <?= htmlspecialchars($receipt['category_icon'] ?? 'receipt_long') ?>
                            </span>
                        </div>
                        <div class="stats-receipt-info">
                            <p class="stats-receipt-name"><?= htmlspecialchars($receipt['store_name']) ?></p>
                            <p class="stats-receipt-meta">
                                <?= date('d.m.Y', strtotime($receipt['receipt_date'])) ?>
                                <?php if (!empty($receipt['category_name'])): ?>
                                    • <?= htmlspecialchars($receipt['category_name']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="stats-receipt-amount">
                            <p><?= number_format($receipt['total_amount'], 2, ',', ' ') ?> zł</p>
                        </div>
                        <span class="material-symbols-outlined stats-receipt-arrow">chevron_right</span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="stats-empty">
                <span class="material-symbols-outlined">receipt_long</span>
                <p>Brak paragonów w tym miesiącu</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
</body>
</html>