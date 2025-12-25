<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Logowanie - Smart Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/login.css"/>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo-section">
            <div class="login-logo-icon">
                <span class="material-symbols-outlined" style="font-size: 36px;">account_balance_wallet</span>
            </div>
            <h1 class="login-title">Witaj ponownie</h1>
            <p class="login-subtitle">Zaloguj się, aby zarządzać wydatkami</p>
        </div>

        <!-- Success message after registration -->
        <?php if (isset($_GET['registered'])): ?>
        <div class="login-message login-message-success">
            <p>Rejestracja zakończona! Możesz się teraz zalogować.</p>
        </div>
        <?php endif; ?>

        <!-- Error message -->
        <?php if (isset($error)): ?>
        <div class="login-message login-message-error">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="/login" method="POST" class="login-form" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <!-- Email -->
            <div class="login-input-group">
                <label class="login-label" for="email">Email</label>
                <div class="login-input-wrapper">
                    <span class="material-symbols-outlined login-input-icon">mail</span>
                    <input type="email" name="email" id="email" required
                        class="login-input"
                        placeholder="Wpisz swój email">
                </div>
            </div>

            <!-- Password -->
            <div class="login-input-group">
                <label class="login-label" for="password">Hasło</label>
                <div class="login-input-wrapper">
                    <span class="material-symbols-outlined login-input-icon">lock</span>
                    <input type="password" name="password" id="password" required
                        class="login-input login-input-password"
                        placeholder="Wpisz hasło">
                    <button type="button" class="login-password-toggle" data-target="password">
                        <span class="material-symbols-outlined">visibility_off</span>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <div class="login-submit-wrapper">
                <button type="submit" class="login-submit-btn">
                    Zaloguj się
                </button>
            </div>

            <!-- Divider -->
            <div class="login-divider">
                <span>lub</span>
            </div>

            <!-- Register link -->
            <div class="login-register-link">
                <p>Nie masz konta? <a href="/register">Zarejestruj się</a></p>
            </div>
        </form>

        <!-- Demo credentials -->
        <div class="login-demo-box">
            <p class="login-demo-label">Dane testowe:</p>
            <p class="login-demo-credentials">test@example.com / test123</p>
        </div>
    </div>

    <script src="/public/scripts/common.js"></script>
    <script src="/public/scripts/login.js"></script>
</body>
</html>