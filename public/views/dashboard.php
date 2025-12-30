<?php
/** @var float $monthlyTotal */
/** @var array $budget */
/** @var array $recentReceipts */
/** @var array $expensesSummary */
/** @var array $user */

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// dane do wykresu
$chartData = [];
$monthNames = ['', 'Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'];

// expensesSummary posortowane od najnowszego (z bazki), odwrócić dla wykresu
$summaryReversed = array_reverse($expensesSummary ?? []);

foreach ($summaryReversed as $data) {
    $chartData[] = [
        'label' => $monthNames[(int)$data['month']] ?? '',
        'value' => (float)($data['total'] ?? 0)
    ];
}

// Jeśli nie ma danych z miesięcy 4 to i tak dodać puste słupki
while (count($chartData) < 4) {
    array_unshift($chartData, ['label' => '-', 'value' => 0]);
}

// Znajdź maksymalną wartość dla skalowania
$maxValue = 0;
foreach ($chartData as $d) {
    if ($d['value'] > $maxValue) {
        $maxValue = $d['value'];
    }
}
if ($maxValue == 0) $maxValue = 1000; // domyślna skala
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($pageTitle) ?> - ChronoCash</title>
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/dashboard.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Header -->
        <header class="dashboard-header">
            <div>
                <p class="dashboard-greeting">Witaj,</p>
                <h1 class="dashboard-username"><?= htmlspecialchars($user['name'] ?? 'Użytkownik') ?></h1> <!-- Zapobiega injection scriptu -->
            </div>
            <a href="/settings" class="dashboard-avatar">
                <span class="material-symbols-outlined">person</span>
            </a>
        </header>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Monthly summary -->
        <div class="dashboard-summary-card">
            <p class="dashboard-summary-label">Wydatki w tym miesiącu</p>
            <p class="dashboard-summary-amount">
                <?= number_format($monthlyTotal ?? 0, 2, ',', ' ') ?> <span class="dashboard-summary-currency">zł</span>
            </p>
            
            <!-- Budget -->
            <?php if (!empty($budget) && isset($budget['amount_limit']) && $budget['amount_limit'] > 0): ?>
            <?php 
                $spent = $monthlyTotal ?? 0;
                $limit = (float)$budget['amount_limit'];
                $remaining = $limit - $spent;
                $percentage = min(100, ($spent / $limit) * 100);
                $barClass = $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : '');
            ?>
            <div class="dashboard-budget-section">
                <div class="dashboard-budget-info">
                    <span>Budżet: <?= number_format($limit, 2, ',', ' ') ?> zł</span>
                    <span class="<?= $remaining < 0 ? 'text-danger' : '' ?>">
                        Zostało: <?= number_format($remaining, 2, ',', ' ') ?> zł
                    </span>
                </div>
                <div class="dashboard-budget-bar">
                    <div class="dashboard-budget-progress <?= $barClass ?>" style="width: <?= $percentage ?>%"></div>
                </div>
            </div>
            <?php else: ?>
            <a href="/settings" class="dashboard-budget-link">
                <span class="material-symbols-outlined">add</span>
                Ustaw budżet miesięczny
            </a>
            <?php endif; ?>
        </div>

        <!-- Expense chart -->
        <div class="dashboard-chart-card">
            <div class="dashboard-chart-header">
                <h2 class="dashboard-chart-title">Wydatki miesięczne</h2>
                <span class="dashboard-chart-subtitle">Ostatnie 4 miesiące</span>
            </div>
            
            <!-- Chart Bars - height based on max value -->
            <div class="dashboard-chart-container">
                <?php foreach ($chartData as $index => $data): ?>
                <?php 
                    $heightPercent = $maxValue > 0 ? ($data['value'] / $maxValue) * 100 : 0;
                    $heightPercent = max(8, $heightPercent);
                    $heightPx = max(12, round(140 * $heightPercent / 100));
                    $isCurrentMonth = ($index === count($chartData) - 1);
                ?>
                <div class="dashboard-chart-bar-wrapper">
                    <span class="dashboard-chart-value">
                        <?= $data['value'] > 0 ? number_format($data['value'], 0, ',', ' ') : '-' ?>
                    </span>
                    <div class="dashboard-chart-bar <?= $isCurrentMonth ? 'current' : '' ?>" 
                         style="height: <?= $heightPx ?>px;"></div>
                    <span class="dashboard-chart-label"><?= htmlspecialchars($data['label']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="dashboard-transactions-card">
            <div class="dashboard-transactions-header">
                <h2 class="dashboard-transactions-title">Ostatnie transakcje</h2>
                <a href="/expenses" class="dashboard-transactions-link">Zobacz wszystkie</a>
            </div>
            
            <?php if (!empty($recentReceipts)): ?>
            <ul class="dashboard-transactions-list">
                <?php foreach ($recentReceipts as $receipt): ?>
                <li>
                    <a href="/receipt?id=<?= $receipt['id'] ?>" class="dashboard-transaction-item">
                        <div class="dashboard-transaction-icon">
                            <span class="material-symbols-outlined">
                                <?= htmlspecialchars($receipt['category_icon'] ?? 'receipt_long') ?>
                            </span>
                        </div>
                        <div class="dashboard-transaction-info">
                            <p class="dashboard-transaction-name"><?= htmlspecialchars($receipt['store_name']) ?></p>
                            <p class="dashboard-transaction-meta">
                                <?= date('d.m.Y', strtotime($receipt['receipt_date'])) ?>
                                <?php if (!empty($receipt['category_name'])): ?>
                                    • <?= htmlspecialchars($receipt['category_name']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="dashboard-transaction-amount">
                            <p><?= number_format($receipt['total_amount'], 2, ',', ' ') ?> zł</p>
                        </div>
                        <span class="material-symbols-outlined dashboard-transaction-arrow">chevron_right</span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="dashboard-empty-state">
                <span class="material-symbols-outlined">receipt_long</span>
                <p>Brak transakcji</p>
                <a href="/add-expense" class="btn btn-primary">
                    Dodaj pierwszy wydatek
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
</body>
</html>