<?php
/** @var array $receipt */
/** @var array $items */

$pageTitle = 'Szczegóły paragonu';
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
    <link rel="stylesheet" href="/public/styles/receipt-details.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <div class="receipt-details-wrapper">
        <!-- Header -->
        <div class="receipt-details-header">
            <a href="javascript:history.back()" class="receipt-details-back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="receipt-details-title">Szczegóły paragonu</h1>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Receipt Card -->
        <div class="receipt-details-main-card">
            <div class="receipt-details-card-header">
                <div>
                    <h2 class="receipt-details-store-name"><?= htmlspecialchars($receipt['store_name']) ?></h2>
                    <p class="receipt-details-date">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <?= date('d.m.Y', strtotime($receipt['receipt_date'])) ?>
                    </p>
                </div>
                <div class="receipt-details-total">
                    <p><?= number_format($receipt['total_amount'], 2, ',', ' ') ?> zł</p>
                </div>
            </div>

            <?php if (!empty($receipt['notes'])): ?>
            <div class="receipt-details-notes">
                <p>
                    <span class="material-symbols-outlined">notes</span>
                    <?= htmlspecialchars($receipt['notes']) ?>
                </p>
            </div>
            <?php endif; ?>

            <?php if (!empty($receipt['receipt_image_path'])): ?>
            <div class="receipt-details-image-link">
                <a href="<?= htmlspecialchars($receipt['receipt_image_path']) ?>" target="_blank">
                    <span class="material-symbols-outlined">image</span>
                    Zobacz zdjęcie paragonu
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Products List -->
        <div class="receipt-details-products-card">
            <div class="receipt-details-products-header">
                <h3>
                    <span class="material-symbols-outlined">shopping_cart</span>
                    Produkty (<?= count($items ?? []) ?>)
                </h3>
            </div>

            <?php if (!empty($items) && is_array($items) && count($items) > 0): ?>
            <ul class="receipt-details-products-list">
                <?php foreach ($items as $item): ?>
                <li class="receipt-details-product-item">
                    <div class="receipt-details-product-info">
                        <div class="receipt-details-product-icon" style="background-color: <?= htmlspecialchars($item['color_hex'] ?? '#3B82F6') ?>20">
                            <span class="material-symbols-outlined" style="color: <?= htmlspecialchars($item['color_hex'] ?? '#3B82F6') ?>">
                                <?= htmlspecialchars($item['icon_name'] ?? 'category') ?>
                            </span>
                        </div>
                        <div>
                            <p class="receipt-details-product-name"><?= htmlspecialchars($item['product_name'] ?? '') ?></p>
                            <p class="receipt-details-product-category">
                                <?= htmlspecialchars($item['category_name'] ?? 'Bez kategorii') ?>
                                <?php if (isset($item['quantity']) && $item['quantity'] > 1): ?>
                                    • <?= (int)$item['quantity'] ?> szt.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="receipt-details-product-price">
                        <p class="receipt-details-product-unit-price"><?= number_format((float)($item['price'] ?? 0), 2, ',', ' ') ?> zł</p>
                        <?php if (isset($item['quantity']) && $item['quantity'] > 1): ?>
                        <p class="receipt-details-product-total-price">
                            Razem: <?= number_format((float)($item['price'] ?? 0) * (int)$item['quantity'], 2, ',', ' ') ?> zł
                        </p>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Summary -->
            <div class="receipt-details-products-summary">
                <span class="receipt-details-summary-label">Suma produktów:</span>
                <span class="receipt-details-summary-value">
                    <?php
                    $itemsTotal = 0;
                    foreach ($items as $item) {
                        $price = (float)($item['price'] ?? 0);
                        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                        $itemsTotal += $price * $quantity;
                    }
                    echo number_format($itemsTotal, 2, ',', ' ');
                    ?> zł
                </span>
            </div>
            <?php else: ?>
            <div class="receipt-details-products-empty">
                <span class="material-symbols-outlined">inventory_2</span>
                <p>Brak szczegółowych produktów</p>
                <p class="receipt-details-products-empty-hint">Ten paragon zawiera tylko kwotę całkowitą</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="receipt-details-actions">
            <a href="/receipt/edit?id=<?= (int)$receipt['id'] ?>" class="receipt-details-action-btn primary">
                <span class="material-symbols-outlined">edit</span>
                Edytuj paragon
            </a>
            
            <a href="/daily-expenses?date=<?= htmlspecialchars($receipt['receipt_date']) ?>" class="receipt-details-action-btn secondary">
                <span class="material-symbols-outlined">calendar_today</span>
                Wydatki z tego dnia
            </a>
        </div>
        
        <button type="button" id="deleteBtn" class="receipt-details-delete-btn">
            <span class="material-symbols-outlined">delete</span>
            Usuń paragon
        </button>

        <!-- Hidden delete form -->
        <form id="deleteForm" action="/receipt/delete" method="POST" class="hidden">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="receipt_id" value="<?= (int)$receipt['id'] ?>">
            <input type="hidden" name="return_url" value="/dashboard">
        </form>
    </div>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
    <script>
        document.getElementById('deleteBtn').addEventListener('click', function() {
            if (confirm('Czy na pewno chcesz usunąć ten paragon?\n\nTa operacja jest nieodwracalna.')) {
                document.getElementById('deleteForm').submit();
            }
        });
    </script>
</body>
</html>