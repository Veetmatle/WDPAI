<?php
$pageTitle = 'Panel Administratora';
$activePage = 'admin';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($pageTitle) ?> - ChronoCash</title>
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/admin.css"/>
</head>
<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <h1>
                <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 0.5rem;">admin_panel_settings</span>
                Panel Administratora
            </h1>
            <div class="admin-header-actions">
                <a href="/dashboard" class="admin-btn admin-btn-success">
                    <span class="material-symbols-outlined">apps</span>
                    Przejdź do aplikacji
                </a>
                <a href="/admin/add-user" class="admin-btn admin-btn-primary">
                    <span class="material-symbols-outlined">person_add</span>
                    Dodaj użytkownika
                </a>
                <a href="/logout" class="admin-btn admin-btn-secondary">
                    <span class="material-symbols-outlined">logout</span>
                    Wyloguj
                </a>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Wszyscy użytkownicy</div>
                    <div class="admin-stat-value"><?= count($users ?? []) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Administratorzy</div>
                    <div class="admin-stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['role_id'] == 4)) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Premium</div>
                    <div class="admin-stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['role_id'] == 2)) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Zablokowani</div>
                    <div class="admin-stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['role_id'] == 3)) ?></div>
                </div>
            </div>

            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2 class="admin-table-title">
                        <span class="material-symbols-outlined">group</span>
                        Lista użytkowników
                    </h2>
                </div>
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Szukaj:</label>
                        <input type="text" id="filterName" class="admin-filter-input" placeholder="Imię, nazwisko lub email...">
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Rola:</label>
                        <select id="filterRole" class="admin-filter-select">
                            <option value="">Wszystkie</option>
                            <option value="1">Użytkownik</option>
                            <option value="2">Premium</option>
                            <option value="3">Zablokowany</option>
                            <option value="4">Admin</option>
                        </select>
                    </div>
                    <button type="button" id="filterReset" class="admin-filter-reset">
                        <span class="material-symbols-outlined">refresh</span>
                        Resetuj
                    </button>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Użytkownik</th>
                            <th>Rola</th>
                            <th>Ostatnie logowanie</th>
                            <th>Data rejestracji</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users ?? [] as $user): ?>
                        <tr data-user-id="<?= $user['id'] ?>">
                            <td><?= $user['id'] ?></td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1) . substr($user['surname'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div class="user-details">
                                        <span class="user-name"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></span>
                                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $roleClass = match((int)$user['role_id']) {
                                    4 => 'badge-admin',
                                    3 => 'badge-blocked',
                                    2 => 'badge-premium',
                                    default => 'badge-active'
                                };
                                ?>
                                <span class="badge <?= $roleClass ?>"><?= htmlspecialchars($user['role_display_name'] ?? $user['role_name'] ?? 'Użytkownik') ?></span>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                <span class="last-login"><?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></span>
                                <?php else: ?>
                                <span class="last-login last-login-never">Nigdy</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="last-login"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <select class="role-select" onchange="changeRole(<?= $user['id'] ?>, this.value)" title="Zmień rolę">
                                        <?php foreach ($roles ?? [] as $role): ?>
                                        <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['display_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="action-btn action-btn-delete" title="Usuń" onclick="deleteUser(<?= $user['id'] ?>)">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="/public/scripts/admin.js"></script>
</body>
</html>
