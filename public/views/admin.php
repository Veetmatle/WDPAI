<?php
$pageTitle = 'Panel Administratora';
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
        .admin-wrapper {
            min-height: 100vh;
            background: var(--bg-primary);
            padding-bottom: 2rem;
        }
        .admin-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 1.5rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .admin-header-actions {
            display: flex;
            gap: 0.75rem;
        }
        .admin-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .admin-btn-primary {
            background: #3b82f6;
            color: white;
        }
        .admin-btn-primary:hover {
            background: #2563eb;
        }
        .admin-btn-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .admin-btn-secondary:hover {
            background: rgba(255,255,255,0.2);
        }
        .admin-content {
            padding: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .admin-stat-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid var(--border-color);
        }
        .admin-stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        .admin-stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .admin-table-container {
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        .admin-table-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th {
            text-align: left;
            padding: 0.875rem 1.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
        }
        .admin-table td {
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
        }
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        .admin-table tr:hover {
            background: var(--bg-tertiary);
        }
        .user-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .user-name {
            font-weight: 500;
        }
        .user-email {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-admin {
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
        }
        .badge-blocked {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        .badge-active {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .action-btn .material-symbols-outlined {
            font-size: 18px;
        }
        .action-btn-block {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
        }
        .action-btn-block:hover {
            background: rgba(245, 158, 11, 0.25);
        }
        .action-btn-unblock {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .action-btn-unblock:hover {
            background: rgba(16, 185, 129, 0.25);
        }
        .action-btn-delete {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        .action-btn-delete:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        .action-btn-admin {
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
        }
        .action-btn-admin:hover {
            background: rgba(139, 92, 246, 0.25);
        }
        .last-login {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        .last-login-never {
            font-style: italic;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        @media (max-width: 768px) {
            .admin-table-container {
                overflow-x: auto;
            }
            .admin-table {
                min-width: 700px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <h1>
                <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 0.5rem;">admin_panel_settings</span>
                Panel Administratora
            </h1>
            <div class="admin-header-actions">
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
                    <div class="admin-stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['is_admin'])) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Zablokowani</div>
                    <div class="admin-stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['is_blocked'])) ?></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-label">Aktywni (ostatnie 24h)</div>
                    <div class="admin-stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['last_login'] && strtotime($u['last_login']) > strtotime('-24 hours'))) ?></div>
                </div>
            </div>

            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2 class="admin-table-title">Lista użytkowników</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Użytkownik</th>
                            <th>Status</th>
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
                                    <span class="user-name"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></span>
                                    <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($user['is_admin']): ?>
                                <span class="badge badge-admin">Admin</span>
                                <?php endif; ?>
                                <?php if ($user['is_blocked']): ?>
                                <span class="badge badge-blocked">Zablokowany</span>
                                <?php else: ?>
                                <span class="badge badge-active">Aktywny</span>
                                <?php endif; ?>
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
                                    <button class="action-btn action-btn-admin" title="<?= $user['is_admin'] ? 'Odbierz admina' : 'Nadaj admina' ?>" 
                                            onclick="toggleAdmin(<?= $user['id'] ?>, <?= $user['is_admin'] ? 'false' : 'true' ?>)">
                                        <span class="material-symbols-outlined"><?= $user['is_admin'] ? 'remove_moderator' : 'add_moderator' ?></span>
                                    </button>
                                    <?php if ($user['is_blocked']): ?>
                                    <button class="action-btn action-btn-unblock" title="Odblokuj" onclick="toggleBlock(<?= $user['id'] ?>, false)">
                                        <span class="material-symbols-outlined">lock_open</span>
                                    </button>
                                    <?php else: ?>
                                    <button class="action-btn action-btn-block" title="Zablokuj" onclick="toggleBlock(<?= $user['id'] ?>, true)">
                                        <span class="material-symbols-outlined">block</span>
                                    </button>
                                    <?php endif; ?>
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

    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';

        async function toggleBlock(userId, block) {
            if (!confirm(block ? 'Czy na pewno chcesz zablokować tego użytkownika?' : 'Czy na pewno chcesz odblokować tego użytkownika?')) {
                return;
            }

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('block', block ? '1' : '0');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('/admin/block-user', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert('Błąd: ' + data.error);
                }
            } catch (error) {
                alert('Wystąpił błąd');
            }
        }

        async function deleteUser(userId) {
            if (!confirm('Czy na pewno chcesz usunąć tego użytkownika? Ta operacja jest nieodwracalna!')) {
                return;
            }

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('/admin/delete-user', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert('Błąd: ' + data.error);
                }
            } catch (error) {
                alert('Wystąpił błąd');
            }
        }

        async function toggleAdmin(userId, makeAdmin) {
            if (!confirm(makeAdmin ? 'Czy na pewno chcesz nadać uprawnienia admina?' : 'Czy na pewno chcesz odebrać uprawnienia admina?')) {
                return;
            }

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('is_admin', makeAdmin ? '1' : '0');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('/admin/toggle-admin', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert('Błąd: ' + data.error);
                }
            } catch (error) {
                alert('Wystąpił błąd');
            }
        }
    </script>
</body>
</html>
