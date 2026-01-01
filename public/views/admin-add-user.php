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
    <link rel="stylesheet" href="/public/styles/admin.css"/>
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

            <form method="POST" class="admin-add-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Imię</label>
                        <input type="text" id="name" name="name" class="form-input" required placeholder="Jan" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="surname">Nazwisko</label>
                        <input type="text" id="surname" name="surname" class="form-input" required placeholder="Kowalski" autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="jan@example.com" autocomplete="off">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Hasło</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="8" placeholder="Minimum 8 znaków" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="role_id">Rola użytkownika</label>
                    <select id="role_id" name="role_id" class="form-select" required>
                        <?php foreach ($roles ?? [] as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $role['id'] == 1 ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['display_name']) ?>
                            <?php if (!empty($role['description'])): ?>
                             - <?= htmlspecialchars($role['description']) ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
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
