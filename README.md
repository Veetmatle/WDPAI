# ChronoCash

## Opis aplikacji

Aplikacja webowa do zarządzania finansami osobistymi. 

Główne funkcjonalności:
- Rejestracja i logowanie użytkowników + walidacja
- Dodawanie paragonów z zakupami
- Przypisywanie wydatków do kategorii (np. jedzonko, pub)
- Przeglądanie wydatków w widoku kalendarza (widok miesiąca)
- Statystyki wydatków z podziałem na kategorie i miesiące (+ filtrowane paragony)
- Ustawianie i śledzenie budżetu miesięcznego
- Edycja i usuwanie paragonów (w ramach tranzakcji - elementy + paragon lub nic)

## Stack:

- Backend: PHP 8.x
- Baza danych: PostgreSQL
- Frontend: HTML5, CSS, JS
- Konteneryzacja: Docker, Docker Compose
- Serwer WWW: Nginx
- Panel bazy danych: pgAdmin 4

## Jak odpalić?

1. Sklonuj repozytorium:
```bash
git clone https://github.com/Veetmatle/WDPAI.git
cd WDPAI
```

2. Skopiuj enva i uzupełnij dane:
```bash
cp .env.example .env
```

Przykładowa zawartość .env:
```
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=db_paragony
DB_USERNAME=twoj_user
DB_PASSWORD=twoje_haslo

PGADMIN_EMAIL=admin@example.com
PGADMIN_PASSWORD=admin_password
```

3. Zbuduj i uruchom kontenery:
```bash
docker-compose up -d --build
```

4. Aplikacja dostępna pod adresem:
```
http://localhost:8080
```

5. pgAdmin dostępny pod:
```
http://localhost:5050
```

Dane testowego użytkownika (automat przy budowaniu kontenerów):
- Email: test@example.com
- Hasło: test123

## Struktura projektu

```
WDPAI/
├── docker/                          # Konfiguracja Docker
│   ├── db/
│   │   ├── Dockerfile              # Obraz PostgreSQL
│   │   └── init.sql                # Schemat bazy i dane początkowe
│   ├── nginx/
│   │   ├── Dockerfile              # Obraz Nginx
│   │   └── nginx.conf              # Konfiguracja serwera
│   └── php/
│       └── Dockerfile              # Obraz PHP-FPM
├── public/                          
│   ├── scripts/                    # Skrypty JavaScript
│   │   ├── add-expense.js          # Obsługa dodawania wydatków
│   │   ├── common.js               # Wspólne funkcje JS
│   │   ├── edit-receipt.js         # Edycja paragonów
│   │   ├── login.js                # Walidacja logowania
│   │   ├── register.js             # Walidacja rejestracji
│   │   └── settings.js             # Obsługa ustawień
│   ├── styles/                     
│   │   ├── common.css              # Style wspólne
│   │   ├── dashboard.css           # Styl dashboardu
│   │   ├── calendar.css            # Styl kalendarza
│   │   ├── stats.css               # Styl statystyk
│   │   └── ...                     # Pozostałe style
│   └── views/                      
│       ├── components/
│       │   └── bottom-nav.php      # Dolna nawigacja
│       ├── dashboard.php           # Panel główny
|       |-- admin-add-user.php      # Dodawanie usera przez admina
|       |-- admin.php               # Widok admina
│       ├── calendar.php            # Widok kalendarza
│       ├── stats.php               # Statystyki
│       ├── add-expense.php         # Dodawanie wydatku
│       ├── settings.php            # Ustawienia
│       ├── login.php               # Logowanie
│       ├── register.php            # Rejestracja
│       └── ...                     # Pozostałe widoki
├── src/                             
│   ├── attributes/
│   │   ├── AttributeValidator.php  # Walidator atrybutów PHP 8
│   │   └── HttpMethod.php          # Atrybut metod HTTP
│   ├── controllers/
|   |   |-- AdminController.php     # Controller admina
│   │   ├── AppController.php       # Bazowy kontroler
│   │   ├── SecurityController.php  # Logowanie/rejestracja
│   │   ├── DashboardController.php # Dashboard
│   │   ├── ExpenseController.php   # Zarządzanie wydatkami
│   │   ├── ReceiptController.php   # Zarządzanie paragonami
│   │   ├── CalendarController.php  # Kalendarz
│   │   ├── StatsController.php     # Statystyki
│   │   ├── BudgetController.php    # Budżet i ustawienia
│   │   └── ApiController.php       # Endpointy API do Jsa
│   ├── middleware/
│   │   └── AuthMiddleware.php      # Autoryzacja i sesje
│   ├── model/
│   │   ├── User.php                # Model użytkownika
│   │   ├── Receipt.php             # Model paragonu
│   │   └── ReceiptItem.php         # Model pozycji paragonu
│   ├── repository/
│   │   ├── Repository.php          # Bazowe repozytorium
│   │   ├── UserRepository.php      # Operacje na użytkownikach
│   │   ├── ReceiptRepository.php   # Operacje na paragonach
│   │   ├── CategoryRepository.php  # Operacje na kategoriach
│   │   └── BudgetRepository.php    # Operacje na budżetach
│   └── services/
│       └── OCRService.php          # Mock OCRa
├── tests/
│   └── HttpMethodTest.php          # Testy jednostkowe
├── Database.php                     # Singleton połączenia z bazą
├── Routing.php                      # System routingu
├── index.php                        # Front Controller
├── docker-compose.yaml              # Konfiguracja Docker Compose
├── .env.example                     # Przykład konfiguracji
└── README.md
```

## Architektura aplikacji (Flow)

Aplikacja MVC:

1. Żądanie HTTP trafia do index.php
2. index.php konfiguruje sesję (httponly, ...) i przekazuje ścieżkę do Routing.php
3. Routing.php mapuje URLa na kontroler i akcję
4. Kontroler (np. ExpenseController) dziedziczy po AppController i:
   - Sprawdza autoryzację przez AuthMiddleware
   - Waliduje token CSRF
   - Przetwarza dane wejściowe (sanityzacja przed injectem)
   - Komunikuje się z bazą przez Repository (wzorzec Repository + Singleton)
   - Zwraca widok PHP lub odpowiedź JSON dla API do JSa
5. Repository używa klasy Database (Singleton) do wykonywania zapytań PDO (chroni przed SQL Injection)
6. Widok renderuje HTML z danymi przekazanymi przez kontroler

Przykładowy flow dodawania wydatku:
```
[Użytkownik] -> POST /add-expense
    -> index.php (sesja, routing)
    -> Routing::run('add-expense')
    -> ExpenseController::add()
        -> requireLogin() [AuthMiddleware]
        -> validateCsrf()
        -> sanitize() danych
        -> ReceiptRepository::createReceipt()
            -> Database::connect() [PDO]
            -> Prepared Statement
        -> redirect('/dashboard')
```

## Baza danych

### Schemat bazy danych (relacje tabel)

- users 1:N receipts (user ma wiele paragonów)
- users 1:N budgets (user ma wiele budżetów)
- users 1:N categories (user ma wiele kategorii do wyboru na paragonach)
- receipts 1:N receipt_items (paragon ma wiele pozycji)
- categories 1:N receipt_items (kategoria ma wiele pozycji)

### Trigger after insert on

Przy rejestracji nowego usera automatycznie kopiowane są domyślne kategorie.

### Diagram ERD

<img width="954" height="810" alt="image" src="https://github.com/user-attachments/assets/89d4c281-f0ef-46f2-8879-020df90c7f72" />

## Bezpieczeństwo 

### Hashowanie haseł
- Hasła hashowane algorytmem Argon2id 
- Weryfikacja przez password_verify()

### Ochrona sesji
- Flaga httponly dla ciasteczek sesji (ochrona przed XSS)
- Flaga samesite=Lax (ochrona przed CSRF)
- Tryb strict_mode dla sesji
- Re-generowanie sesji po zalogowaniu (ochrona prze session fixation)

### Ochrona CSRF
- Tokeny CSRF generowane przez bin2hex(random_bytes(32))
- Walidacja tokenów przez hash_equals() 
- Token wymagany we wszystkich formularzach POST

### Ochrona przed SQL Injection
- Wszystkie zapytania przez PDO Prepared Statements
- Parametry bindowane przez bindParam() z określeniem typu

### Walidacja danych wejściowych
- Sanityzacja przez htmlspecialchars() 
- Walidacja emaila 
- Walidacja długości pól wejściowych
- Walidacja złożoności hasła 
- Walidacja imienia/nazwiska 

### Kontrola dostępu
- Middleware AuthMiddleware sprawdza autoryzację
- Użytkownicy mają dostęp tylko do swoich danych 
- Ukrywanie błędów przed użytkownikiem 

## Screenshoty aplikacji

### Rejestracja
<img width="663" height="847" alt="Ekran rejestracji" src="https://github.com/user-attachments/assets/2bb5f69a-13bb-4525-953a-8f9126bb3476" />

### Logowanie
<img width="675" height="837" alt="Ekran logowania" src="https://github.com/user-attachments/assets/16b2e034-a3e0-4c2a-9b3b-6bb3c26c9648" />

### Dashboard
<img width="884" height="956" alt="Panel główny" src="https://github.com/user-attachments/assets/e4708feb-89ff-42ae-9fcf-abf6481227da" />

### Kalendarz
<img width="1898" height="960" alt="Widok kalendarza" src="https://github.com/user-attachments/assets/9741e716-9866-43e2-8030-26a66fb002da" />

### Statystyki
<img width="823" height="958" alt="Statystyki wydatków" src="https://github.com/user-attachments/assets/c9cc4373-f528-4d50-8ba2-23076bd0258a" />

### Statystyki - wydatki miesięczne
<img width="768" height="963" alt="Wydatki za dany miesiąc" src="https://github.com/user-attachments/assets/30061820-60ea-4192-9b63-89e45613e931" />

### Ustawienia
<img width="709" height="957" alt="Panel ustawień" src="https://github.com/user-attachments/assets/5c4b1a27-ab15-43d9-baf2-ac15ac8a879a" />

<img width="732" height="960" alt="Panel ustawień - rozszerzony" src="https://github.com/user-attachments/assets/dccae258-10bf-4a99-b40d-d2c4f9906d2b" />

### Dodawanie wydatku
<img width="736" height="958" alt="Formularz dodawania wydatku" src="https://github.com/user-attachments/assets/8c5dc407-1745-4bac-b3c2-3f9dc27c6437" />

### Paragon
<img width="680" height="957" alt="Widok paragonu" src="https://github.com/user-attachments/assets/a40d58e1-9850-4dd3-b0a9-da2d99088e02" />

### Wydatki dzienne
<img width="635" height="958" alt="Lista wydatków dziennych" src="https://github.com/user-attachments/assets/356d49ab-c782-41fd-b27b-8a10b49b3497" />

### Edycja paragonu
<img width="695" height="953" alt="Edycja paragonu" src="https://github.com/user-attachments/assets/18326894-902a-458f-9f19-a55fe0f3690d" />

### Widok admina
<img width="1892" height="934" alt="image" src="https://github.com/user-attachments/assets/3ba2b5d9-7903-4f07-b9f5-d183a7d2234f" />

### Dodawanie usera przez admina
<img width="1908" height="908" alt="image" src="https://github.com/user-attachments/assets/48cb0931-f438-4fee-bbad-8f06bd6a24de" />

### Zablokowany user
<img width="632" height="845" alt="image" src="https://github.com/user-attachments/assets/caaee115-d571-427d-ae69-ebc2cc50a054" />




