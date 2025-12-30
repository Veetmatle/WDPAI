<?php
$pageTitle = 'Dodaj użytkownika';
$activePage = 'admin';
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
    <style>
        .admin-add-wrapper {
            min-height: 100vh;
            background: var(--bg-primary);
            padding-bottom: 2rem;
        }
        .admin-add-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .admin-add-header a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .admin-add-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .admin-add-content {
            padding: 1.5rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .admin-add-form {
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .form-checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .form-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .form-checkbox-label {
            font-size: 0.875rem;
            color: var(--text-primary);
            cursor: pointer;
        }
        .form-checkbox-hint {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .form-submit {
            width: 100%;
            padding: 0.875rem;
            border-radius: 8px;
            border: none;
            background: #3b82f6;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .form-submit:hover {
            background: #2563eb;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 500px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-add-wrapper">
        <header class="admin-add-header">
            <a href="/admin">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1>Dodaj użytkownika</h1>
        </header>

        <div class="admin-add-content">
            <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="admin-add-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Imię</label>
                        <input type="text" id="name" name="name" class="form-input" required placeholder="Jan">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="surname">Nazwisko</label>
                        <input type="text" id="surname" name="surname" class="form-input" required placeholder="Kowalski">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="jan@example.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Hasło</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="8" placeholder="Minimum 8 znaków">
                </div>

                <div class="form-group">
                    <div class="form-checkbox-group">
                        <input type="checkbox" id="is_admin" name="is_admin" value="1" class="form-checkbox">
                        <div>
                            <label class="form-checkbox-label" for="is_admin">Uprawnienia administratora</label>
                            <p class="form-checkbox-hint">Użytkownik będzie mógł zarządzać innymi użytkownikami</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="form-submit">
                    <span class="material-symbols-outlined">person_add</span>
                    Utwórz użytkownika
                </button>
            </form>
        </div>
    </div>
</body>
</html>
