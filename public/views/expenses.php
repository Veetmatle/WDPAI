<?php
/** @var array $groupedReceipts */
/** @var string $pageTitle */
/** @var int|null $filterMonth */
/** @var int|null $filterYear */

$activePage = 'expenses';

$monthNames = [
    1 => 'stycznia', 2 => 'lutego', 3 => 'marca', 4 => 'kwietnia',
    5 => 'maja', 6 => 'czerwca', 7 => 'lipca', 8 => 'sierpnia',
    9 => 'września', 10 => 'października', 11 => 'listopada', 12 => 'grudnia'
];
$dayNames = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];

// Ustaw domyślny tytuł jeśli nie przekazano
$pageTitle = $pageTitle ?? 'Wszystkie wydatki';
$backUrl = ($filterMonth && $filterYear) ? "/stats?month={$filterMonth}&year={$filterYear}" : '/dashboard';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($pageTitle) ?> - Smart Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/expenses.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="expenses-wrapper">
        <!-- Header -->
        <div class="expenses-header">
            <a href="<?= htmlspecialchars($backUrl) ?>" class="expenses-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="expenses-title"><?= htmlspecialchars($pageTitle) ?></h1>
        </div>

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

        <?php if (!empty($groupedReceipts)): ?>
        <div class="expenses-list">
            <?php foreach ($groupedReceipts as $dateKey => $dayData): ?>
            <?php 
                $timestamp = strtotime($dateKey);
                $dayName = $dayNames[date('w', $timestamp)];
                $formattedDate = date('d', $timestamp) . ' ' . $monthNames[(int)date('n', $timestamp)] . ' ' . date('Y', $timestamp);
            ?>
            <div class="expenses-day-group">
                <!-- Date Header -->
                <div class="expenses-day-header">
                    <div>
                        <p class="expenses-day-name"><?= htmlspecialchars($dayName) ?></p>
                        <p class="expenses-day-date"><?= htmlspecialchars($formattedDate) ?></p>
                    </div>
                    <div class="expenses-day-total">
                        <p><?= number_format($dayData['total'], 2, ',', ' ') ?> zł</p>
                    </div>
                </div>

                <!-- Receipts for this day -->
                <ul class="expenses-receipt-list">
                    <?php foreach ($dayData['receipts'] as $receipt): ?>
                    <li>
                        <a href="/receipt?id=<?= (int)$receipt['id'] ?>" class="expenses-receipt-item">
                            <div class="expenses-receipt-icon">
                                <span class="material-symbols-outlined">receipt_long</span>
                            </div>
                            <div class="expenses-receipt-info">
                                <p class="expenses-receipt-name"><?= htmlspecialchars($receipt['store_name']) ?></p>
                                <?php if (!empty($receipt['notes'])): ?>
                                <p class="expenses-receipt-notes"><?= htmlspecialchars($receipt['notes']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="expenses-receipt-amount">
                                <p><?= number_format((float)$receipt['total_amount'], 2, ',', ' ') ?> zł</p>
                            </div>
                            <span class="material-symbols-outlined expenses-receipt-arrow">chevron_right</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="expenses-empty">
            <span class="material-symbols-outlined">receipt_long</span>
            <p class="expenses-empty-title">Brak wydatków</p>
            <p class="expenses-empty-text">Nie masz jeszcze żadnych zarejestrowanych wydatków</p>
            <a href="/add-expense" class="btn btn-primary">
                <span class="material-symbols-outlined">add</span>
                Dodaj pierwszy wydatek
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
</body>
</html>