<?php
/** @var array $receipt */
/** @var array $items */
/** @var array $categories */
$pageTitle = 'Edytuj paragon';
$activePage = '';
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
    <link rel="stylesheet" href="/public/styles/edit-receipt.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="edit-receipt-wrapper">
        <!-- Header -->
        <div class="edit-receipt-header">
            <a href="/receipt?id=<?= (int)$receipt['id'] ?>" class="edit-receipt-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="edit-receipt-title">Edytuj paragon</h1>
        </div>

        <!-- Form -->
        <form id="editForm" method="POST" class="edit-receipt-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="receipt_id" value="<?= (int)$receipt['id'] ?>">

            <!-- Store Name -->
            <div class="edit-receipt-field">
                <label class="edit-receipt-label">Nazwa sklepu</label>
                <input type="text" name="store_name" id="store_name" required
                       value="<?= htmlspecialchars($receipt['store_name']) ?>"
                       class="edit-receipt-input"
                       placeholder="np. Biedronka, Lidl, Żabka">
            </div>

            <!-- Date -->
            <div class="edit-receipt-field">
                <label class="edit-receipt-label">Data zakupu</label>
                <input type="date" name="receipt_date" id="receipt_date" required
                       value="<?= htmlspecialchars($receipt['receipt_date']) ?>"
                       max="<?= date('Y-m-d') ?>"
                       class="edit-receipt-input">
            </div>

            <!-- Notes -->
            <div class="edit-receipt-field">
                <label class="edit-receipt-label">Notatka (opcjonalnie)</label>
                <input type="text" name="notes" id="notes"
                       value="<?= htmlspecialchars($receipt['notes'] ?? '') ?>"
                       class="edit-receipt-input"
                       placeholder="np. Zakupy tygodniowe">
            </div>

            <!-- Items Section -->
            <div class="edit-receipt-items-section">
                <div class="edit-receipt-items-header">
                    <label class="edit-receipt-label">Produkty</label>
                    <button type="button" id="add-item-btn" class="edit-receipt-add-item-btn">
                        <span class="material-symbols-outlined">add</span>
                        Dodaj produkt
                    </button>
                </div>
                <div id="items-container" class="edit-receipt-items-container">
                    <!-- Existing items will be loaded here -->
                </div>
                <p id="no-items-msg" class="edit-receipt-no-items hidden">
                    Kliknij "Dodaj produkt" aby dodać pozycje
                </p>
            </div>

            <!-- Total Amount -->
            <div class="edit-receipt-total-section">
                <div class="edit-receipt-total-row">
                    <label class="edit-receipt-total-label">Kwota całkowita</label>
                    <div class="edit-receipt-total-input-wrapper">
                        <input type="number" name="total_amount" id="total_amount" step="0.01" min="0" required
                               value="<?= number_format((float)$receipt['total_amount'], 2, '.', '') ?>"
                               class="edit-receipt-total-input"
                               placeholder="0.00">
                        <span class="edit-receipt-total-currency">zł</span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="edit-receipt-actions">
                <a href="/receipt?id=<?= (int)$receipt['id'] ?>" class="edit-receipt-cancel-btn">
                    Anuluj
                </a>
                <button type="submit" class="edit-receipt-submit-btn">
                    <span class="material-symbols-outlined">save</span>
                    Zapisz zmiany
                </button>
            </div>
        </form>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
    <script>
        const categories = <?= json_encode($categories ?? []) ?>;
        const existingItems = <?= json_encode($items ?? []) ?>;
        const receiptId = <?= (int)$receipt['id'] ?>;
    </script>
    <script src="/public/scripts/edit-receipt.js"></script>
</body>
</html>