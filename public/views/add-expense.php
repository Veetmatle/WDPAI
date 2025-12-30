<?php
/** @var array $categories */
/** @var array $budget */
$pageTitle = 'Dodaj wydatek';
$activePage = 'add';

// Pobierz datę z parametru URL lub ustaw dzisiejszą
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Walidacja formatu daty
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    $selectedDate = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($pageTitle) ?> - ChronoCashr</title>
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/add-expense.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="add-expense-wrapper">
        <!-- Header -->
        <div class="add-expense-header">
            <a href="javascript:history.back()" class="add-expense-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="add-expense-title">Dodaj wydatek</h1>
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

        <!-- Budget Alert -->
        <?php if (!empty($budget) && isset($budget['amount_limit']) && $budget['amount_limit'] > 0): ?>
        <?php 
            $spent = $budget['spent'] ?? 0;
            $limit = $budget['amount_limit'] ?? 0;
            $remaining = $limit - $spent;
            $percentage = $limit > 0 ? min(100, ($spent / $limit) * 100) : 0;
            $barClass = $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success');
        ?>
        <div class="add-expense-budget-alert">
            <div class="add-expense-budget-header">
                <span class="add-expense-budget-label">Budżet miesięczny</span>
                <span class="add-expense-budget-remaining <?= $remaining < 0 ? 'danger' : 'success' ?>">
                    Zostało: <?= number_format($remaining, 2, ',', ' ') ?> zł
                </span>
            </div>
            <div class="add-expense-budget-bar">
                <div class="add-expense-budget-progress <?= $barClass ?>" style="width: <?= $percentage ?>%"></div>
            </div>
            <p class="add-expense-budget-info">
                <?= number_format($spent, 2, ',', ' ') ?> / <?= number_format($limit, 2, ',', ' ') ?> zł
            </p>
        </div>
        <?php endif; ?>

        <!-- Mode Switcher -->
        <div class="add-expense-mode-switcher">
            <button type="button" id="btn-manual" class="add-expense-mode-btn active" data-mode="manual">
                <span class="material-symbols-outlined">edit</span>
                Ręcznie
            </button>
            <button type="button" id="btn-ocr" class="add-expense-mode-btn" data-mode="ocr">
                <span class="material-symbols-outlined">document_scanner</span>
                Skanuj paragon
            </button>
        </div>

        <!-- OCR Section -->
        <div id="ocr-section" class="add-expense-ocr-section hidden">
            <div class="add-expense-ocr-upload">
                <input type="file" id="receipt-image" accept="image/*" class="hidden">
                <label for="receipt-image" class="add-expense-ocr-label">
                    <span class="material-symbols-outlined add-expense-ocr-icon">add_a_photo</span>
                    <p>Kliknij lub przeciągnij zdjęcie paragonu</p>
                    <p class="add-expense-ocr-hint">JPG, PNG do 5MB</p>
                </label>
                <div id="image-preview" class="add-expense-image-preview hidden">
                    <img id="preview-img" src="" alt="Podgląd">
                    <button type="button" id="process-btn" class="add-expense-process-btn">
                        <span class="material-symbols-outlined">search</span>
                        Analizuj paragon
                    </button>
                </div>
            </div>
            <div id="ocr-loading" class="add-expense-ocr-loading hidden">
                <div class="add-expense-spinner"></div>
                <p>Analizowanie paragonu...</p>
            </div>
        </div>

        <!-- Manual Form -->
        <form id="expenseForm" method="POST" class="add-expense-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <!-- Store Name -->
            <div class="add-expense-field">
                <label class="add-expense-label">Nazwa sklepu</label>
                <input type="text" name="store_name" id="store_name" required
                       class="add-expense-input"
                       placeholder="np. Biedronka, Lidl, Żabka">
            </div>

            <!-- Date -->
            <div class="add-expense-field">
                <label class="add-expense-label">Data zakupu</label>
                <input type="date" name="receipt_date" id="receipt_date" required
                       value="<?= htmlspecialchars($selectedDate) ?>"
                       max="<?= date('Y-m-d') ?>"
                       class="add-expense-input">
            </div>

            <!-- Notes -->
            <div class="add-expense-field">
                <label class="add-expense-label">Notatka (opcjonalnie)</label>
                <input type="text" name="notes" id="notes"
                       class="add-expense-input"
                       placeholder="np. Zakupy tygodniowe">
            </div>

            <!-- Items Section -->
            <div class="add-expense-items-section">
                <div class="add-expense-items-header">
                    <label class="add-expense-label">Produkty</label>
                    <button type="button" id="add-item-btn" class="add-expense-add-item-btn">
                        <span class="material-symbols-outlined">add</span>
                        Dodaj produkt
                    </button>
                </div>
                <div id="items-container" class="add-expense-items-container">
                    <!-- Dynamic items will be added here -->
                </div>
                <p id="no-items-msg" class="add-expense-no-items">
                    Kliknij "Dodaj produkt" lub podaj tylko kwotę całkowitą poniżej
                </p>
            </div>

            <!-- Total Amount -->
            <div class="add-expense-total-section">
                <div class="add-expense-total-row">
                    <label class="add-expense-total-label">Kwota całkowita</label>
                    <div class="add-expense-total-input-wrapper">
                        <input type="number" name="total_amount" id="total_amount" step="0.01" min="0" required
                               class="add-expense-total-input"
                               placeholder="0.00">
                        <span class="add-expense-total-currency">zł</span>
                    </div>
                </div>
                <p class="add-expense-total-info" id="auto-sum-info">
                    Kwota zostanie automatycznie obliczona z produktów
                </p>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="add-expense-submit-btn">
                <span class="material-symbols-outlined">save</span>
                Zapisz wydatek
            </button>
        </form>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
    <script>
        const categories = <?= json_encode($categories ?? []) ?>;
        const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    </script>
    <script src="/public/scripts/add-expense.js"></script>
</body>
</html>