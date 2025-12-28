<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>404 - Nie znaleziono - ChronoCash</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="/public/styles/common.css"/>
    <link rel="stylesheet" href="/public/styles/404.css"/>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <!-- 404 Icon -->
            <div class="error-icon-wrapper">
                <span class="material-symbols-outlined error-icon-bg">search_off</span>
                <span class="error-code">404</span>
            </div>

            <h1 class="error-title">Strona nie znaleziona</h1>
            <p class="error-message">
                Ups! Wygląda na to, że ta strona zniknęła szybciej niż Twoje wydatki na kawę ☕
            </p>

            <div class="error-actions">
                <a href="/dashboard" class="error-btn error-btn-primary">
                    <span class="material-symbols-outlined">home</span>
                    Wróć do panelu
                </a>
                <a href="javascript:history.back()" class="error-btn error-btn-secondary">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Poprzednia strona
                </a>
            </div>

            <!-- Fun fact -->
            <div class="error-fun-fact">
                <p>
                    <span class="material-symbols-outlined">lightbulb</span>
                    <strong>Ciekawostka:</strong> 
                    Kajak od tyłu to kajak.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="error-footer">
            <p>ChronoCash, ale nazwa do zmiany będzie</p>
        </div>
    </div>
</body>
</html>