<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Rejestracja - Smart Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/register.css"/>
</head>
<body>
    <div class="register-container">
        <!-- Logo -->
        <div class="register-logo-section">
            <div class="register-logo-icon">
                <span class="material-symbols-outlined" style="font-size: 36px;">account_balance_wallet</span>
            </div>
            <h1 class="register-title">Utwórz konto</h1>
            <p class="register-subtitle">Zacznij kontrolować swoje wydatki</p>
        </div>

        <!-- Error message -->
        <?php if (isset($error)): ?>
        <div class="register-error">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="/register" method="POST" class="register-form" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <!-- Name row -->
            <div class="register-name-row">
                <div class="register-input-group">
                    <label class="register-label" for="name">Imię</label>
                    <input type="text" name="name" id="name" required minlength="2" maxlength="100"
                        class="register-input"
                        placeholder="Jan">
                </div>
                <div class="register-input-group">
                    <label class="register-label" for="surname">Nazwisko</label>
                    <input type="text" name="surname" id="surname" required minlength="2" maxlength="100"
                        class="register-input"
                        placeholder="Kowalski">
                </div>
            </div>

            <!-- Email -->
            <div class="register-input-group">
                <label class="register-label" for="email">Email</label>
                <div class="register-input-wrapper">
                    <span class="material-symbols-outlined register-input-icon">mail</span>
                    <input type="email" name="email" id="email" required maxlength="255"
                        class="register-input register-input-with-icon"
                        placeholder="jan@example.com">
                </div>
            </div>

            <!-- Password -->
            <div class="register-input-group">
                <label class="register-label" for="password">Hasło</label>
                <div class="register-input-wrapper">
                    <span class="material-symbols-outlined register-input-icon">lock</span>
                    <input type="password" name="password" id="password" required minlength="8"
                        class="register-input register-input-with-icon register-input-password"
                        placeholder="Minimum 8 znaków">
                    <button type="button" class="register-password-toggle" data-target="password">
                        <span class="material-symbols-outlined">visibility_off</span>
                    </button>
                </div>
                <div id="passwordStrength" class="register-password-strength hidden">
                    <div class="register-strength-bars">
                        <div class="register-strength-bar" id="str1"></div>
                        <div class="register-strength-bar" id="str2"></div>
                        <div class="register-strength-bar" id="str3"></div>
                        <div class="register-strength-bar" id="str4"></div>
                    </div>
                    <p class="register-password-hint" id="passwordHint"></p>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="register-input-group">
                <label class="register-label" for="password_confirm">Potwierdź hasło</label>
                <div class="register-input-wrapper">
                    <span class="material-symbols-outlined register-input-icon">lock</span>
                    <input type="password" name="password_confirm" id="password_confirm" required
                        class="register-input register-input-with-icon register-input-password"
                        placeholder="Powtórz hasło">
                    <button type="button" class="register-password-toggle" data-target="password_confirm">
                        <span class="material-symbols-outlined">visibility_off</span>
                    </button>
                </div>
                <p id="passwordMatchError" class="register-match-error hidden">Hasła nie są identyczne</p>
            </div>

            <!-- Submit -->
            <div class="register-submit-wrapper">
                <button type="submit" class="register-submit-btn" id="submitBtn">
                    Zarejestruj się
                </button>
            </div>

            <!-- Login link -->
            <div class="register-login-link">
                <p>Masz już konto? <a href="/login">Zaloguj się</a></p>
            </div>
        </form>
    </div>

    <script src="/public/scripts/common.js"></script>
    <script src="/public/scripts/register.js"></script>
</body>
</html>