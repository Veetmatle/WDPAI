<?php
/**
 * Bottom Navigation Component
 * @var string $activePage - current active page (dashboard, calendar, add, stats, settings)
 */
$activePage = $activePage ?? '';
?>
<nav class="bottom-nav">
    <div class="bottom-nav-container">
        <div class="bottom-nav-items">
            <!-- Home -->
            <a href="/dashboard" class="bottom-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">home</span>
                <span class="bottom-nav-label">Główna</span>
            </a>
            
            <!-- Calendar -->
            <a href="/calendar" class="bottom-nav-item <?= $activePage === 'calendar' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">calendar_month</span>
                <span class="bottom-nav-label">Kalendarz</span>
            </a>
            
            <!-- Add Button (center, elevated) -->
            <a href="/add-expense" class="bottom-nav-add-btn <?= $activePage === 'add' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">add</span>
            </a>
            
            <!-- Spacer for center button -->
            <div class="bottom-nav-spacer"></div>
            
            <!-- Stats -->
            <a href="/stats" class="bottom-nav-item <?= $activePage === 'stats' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">bar_chart</span>
                <span class="bottom-nav-label">Statystyki</span>
            </a>
            
            <!-- Settings -->
            <a href="/settings" class="bottom-nav-item <?= $activePage === 'settings' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">settings</span>
                <span class="bottom-nav-label">Ustawienia</span>
            </a>
        </div>
    </div>
</nav>