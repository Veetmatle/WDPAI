<?php
$pageTitle = 'Wydatki dnia';
$activePage = 'calendar';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Wydatki dnia - Smart Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/daily-expenses.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="daily-expenses-wrapper">
        <!-- Header -->
        <header class="daily-expenses-header">
            <a href="/calendar" class="daily-expenses-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="daily-expenses-header-title">Wydatki dnia</h2>
            <div class="daily-expenses-header-spacer"></div>
        </header>

        <main class="daily-expenses-main">
            <!-- Date & Total -->
            <div class="daily-expenses-summary">
                <h1 class="daily-expenses-date">
                    <?= htmlspecialchars($formattedDate ?? date('d.m.Y')) ?>
                </h1>
                <p class="daily-expenses-total">
                    -<?= number_format($dailyTotal ?? 0, 2, ',', ' ') ?> zł
                </p>
            </div>

            <!-- Receipts List -->
            <div class="daily-expenses-list">
                <?php if (empty($receipts)): ?>
                <div class="daily-expenses-empty">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <p class="daily-expenses-empty-title">Brak wydatków tego dnia</p>
                    <a href="/add-expense?date=<?= htmlspecialchars($date ?? date('Y-m-d')) ?>" class="daily-expenses-empty-link">
                        <span class="material-symbols-outlined">add_circle</span>
                        Dodaj wydatek
                    </a>
                </div>
                <?php else: ?>
                <?php foreach ($receipts as $receipt): ?>
                <div class="daily-expenses-card">
                    <a href="/receipt?id=<?= (int)$receipt['id'] ?>" class="daily-expenses-card-link">
                        <div class="daily-expenses-card-icon">
                            <span class="material-symbols-outlined"><?= htmlspecialchars($receipt['icon_name'] ?? 'receipt_long') ?></span>
                        </div>
                        <div class="daily-expenses-card-info">
                            <p class="daily-expenses-card-name">
                                <?= htmlspecialchars($receipt['store_name']) ?>
                            </p>
                            <p class="daily-expenses-card-time">
                                <?= date('H:i', strtotime($receipt['created_at'])) ?>
                            </p>
                        </div>
                        <div class="daily-expenses-card-amount">
                            <p>-<?= number_format($receipt['total_amount'], 2, ',', ' ') ?> zł</p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <!-- FAB Button -->
        <a href="/add-expense?date=<?= htmlspecialchars($date ?? date('Y-m-d')) ?>" class="daily-expenses-fab">
            <span class="material-symbols-outlined">add</span>
        </a>

        <!-- Bottom Navigation -->
        <?php include __DIR__ . '/components/bottom-nav.php'; ?>
    </div>

    <script src="/public/scripts/common.js"></script>
</body>
</html>