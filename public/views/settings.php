<?php
$pageTitle = 'Ustawienia';
$activePage = 'settings';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Ustawienia - ChronoCash</title>
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/settings.css"/>
    <link rel="stylesheet" href="/public/styles/bottom-nav.css"/>
</head>
<body>
    <!-- Header -->
    <header class="settings-header">
        <div class="settings-header-inner">
            <h1 class="settings-header-title">Ustawienia</h1>
        </div>
    </header>

    <main class="settings-main">
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <p><?= htmlspecialchars($_GET['success']) ?></p>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <p><?= htmlspecialchars($_GET['error']) ?></p>
        </div>
        <?php endif; ?>

        <!-- User Profile Section -->
        <section class="settings-section">
            <div class="settings-profile-header">
                <div class="settings-avatar">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div class="settings-profile-info">
                    <h2 class="settings-profile-name">
                        <?= htmlspecialchars(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')) ?>
                    </h2>
                    <p class="settings-profile-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                </div>
            </div>

            <form action="/settings/profile" method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                
                <div class="settings-name-row">
                    <div class="settings-field">
                        <label class="settings-label">Imię</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required
                               class="settings-input">
                    </div>
                    <div class="settings-field">
                        <label class="settings-label">Nazwisko</label>
                        <input type="text" name="surname" value="<?= htmlspecialchars($user['surname'] ?? '') ?>" required
                               class="settings-input">
                    </div>
                </div>

                <button type="submit" class="settings-btn settings-btn-primary">
                    Zapisz profil
                </button>
            </form>
        </section>

        <!-- Budget Section -->
        <section class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon warning">
                    <span class="material-symbols-outlined">savings</span>
                </div>
                <div>
                    <h3 class="settings-section-title">Budżet miesięczny</h3>
                    <p class="settings-section-subtitle">Ustaw limit wydatków na miesiąc</p>
                </div>
            </div>

            <?php if (isset($budget) && $budget): ?>
            <div class="settings-budget-display">
                <div class="settings-budget-row">
                    <span class="settings-budget-label">Aktualny limit</span>
                    <span class="settings-budget-value"><?= number_format($budget['limit'], 2, ',', ' ') ?> zł</span>
                </div>
                <div class="settings-budget-row">
                    <span class="settings-budget-label">Wydano</span>
                    <span class="settings-budget-spent"><?= number_format($budget['spent'], 2, ',', ' ') ?> zł</span>
                </div>
                <?php 
                $percentage = min(100, $budget['percentage']);
                $barClass = $percentage < 70 ? 'success' : ($percentage < 90 ? 'warning' : 'danger');
                ?>
                <div class="settings-budget-bar">
                    <div class="settings-budget-progress <?= $barClass ?>" style="width: <?= $percentage ?>%"></div>
                </div>
            </div>
            <?php endif; ?>

            <form action="/settings/budget" method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                
                <div class="settings-field">
                    <label class="settings-label">Nowy limit (zł)</label>
                    <div class="settings-input-wrapper">
                        <span class="material-symbols-outlined settings-input-icon">payments</span>
                        <input type="number" name="amount_limit" step="0.01" min="1" required
                               value="<?= htmlspecialchars($budget['limit'] ?? '3000') ?>"
                               class="settings-input settings-input-with-icon">
                    </div>
                </div>

                <button type="submit" class="settings-btn settings-btn-warning">
                    Ustaw budżet
                </button>
            </form>
        </section>

        <!-- Change Password Section -->
        <section class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon danger">
                    <span class="material-symbols-outlined">lock</span>
                </div>
                <div>
                    <h3 class="settings-section-title">Zmień hasło</h3>
                    <p class="settings-section-subtitle">Zaktualizuj swoje hasło</p>
                </div>
            </div>

            <form action="/settings/password" method="POST" class="settings-form" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                
                <div class="settings-field">
                    <label class="settings-label">Aktualne hasło</label>
                    <div class="settings-input-wrapper">
                        <span class="material-symbols-outlined settings-input-icon">lock</span>
                        <input type="password" name="current_password" id="currentPassword" required
                               placeholder=" "
                               class="settings-input settings-input-with-icon settings-input-password">
                        <button type="button" class="settings-password-toggle" data-target="currentPassword">
                            <span class="material-symbols-outlined">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div class="settings-field">
                    <label class="settings-label">Nowe hasło</label>
                    <div class="settings-input-wrapper">
                        <span class="material-symbols-outlined settings-input-icon">key</span>
                        <input type="password" name="new_password" id="newPassword" required
                               placeholder=" "
                               class="settings-input settings-input-with-icon settings-input-password">
                        <button type="button" class="settings-password-toggle" data-target="newPassword">
                            <span class="material-symbols-outlined">visibility_off</span>
                        </button>
                    </div>
                    <!-- Password strength indicator -->
                    <div class="settings-strength-bars">
                        <div id="str1" class="settings-strength-bar"></div>
                        <div id="str2" class="settings-strength-bar"></div>
                        <div id="str3" class="settings-strength-bar"></div>
                        <div id="str4" class="settings-strength-bar"></div>
                    </div>
                    <p id="strengthText" class="settings-strength-text"></p>
                </div>

                <div class="settings-field">
                    <label class="settings-label">Potwierdź nowe hasło</label>
                    <div class="settings-input-wrapper">
                        <span class="material-symbols-outlined settings-input-icon">key</span>
                        <input type="password" name="confirm_password" id="confirmPassword" required
                               placeholder=" "
                               class="settings-input settings-input-with-icon settings-input-password">
                        <button type="button" class="settings-password-toggle" data-target="confirmPassword">
                            <span class="material-symbols-outlined">visibility_off</span>
                        </button>
                    </div>
                    <p id="matchText" class="settings-match-text hidden"></p>
                </div>

                <button type="submit" class="settings-btn settings-btn-danger">
                    Zmień hasło
                </button>
            </form>
        </section>

        <!-- Pro Features -->
        <section class="settings-pro-section">
            <div class="settings-section-header">
                <div class="settings-section-icon pro">
                    <span class="material-symbols-outlined">auto_awesome</span>
                </div>
                <div>
                    <h3 class="settings-section-title">Smart Expense Pro</h3>
                    <p class="settings-section-subtitle">Odblokuj zaawansowane funkcje</p>
                </div>
            </div>
            <ul class="settings-pro-features">
                <li>
                    <span class="material-symbols-outlined">check_circle</span>
                    Zaawansowane raporty i analizy
                </li>
                <li>
                    <span class="material-symbols-outlined">check_circle</span>
                    Eksport do PDF i Excel
                </li>
                <li>
                    <span class="material-symbols-outlined">check_circle</span>
                    Nieograniczone kategorie
                </li>
                <li>
                    <span class="material-symbols-outlined">check_circle</span>
                    Synchronizacja między urządzeniami
                </li>
            </ul>
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank" class="settings-pro-btn">
                <span class="material-symbols-outlined">rocket_launch</span>
                Odblokuj Pro
            </a>
        </section>

        <!-- Logout -->
        <form action="/logout" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <button type="submit" class="settings-logout-btn">
                <span class="material-symbols-outlined">logout</span>
                Wyloguj się
            </button>
        </form>

        <!-- App Info -->
        <div class="settings-app-info">
            <p>Smart Expense Tracker v1.0</p>
            <p>© 2024 - Projekt akademicki WdPAI</p>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <?php include __DIR__ . '/components/bottom-nav.php'; ?>

    <script src="/public/scripts/common.js"></script>
    <script src="/public/scripts/settings.js"></script>
</body>
</html>