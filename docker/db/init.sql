-- =====================================================
-- Baza danych apki do paragonów
-- =====================================================

-- Czyszczenie bazy 
DROP TABLE IF EXISTS receipt_items CASCADE;
DROP TABLE IF EXISTS receipts CASCADE;
DROP TABLE IF EXISTS budgets CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- =====================================================
-- USERS
-- =====================================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    surname VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- CATEGORIES
-- =====================================================
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    icon_name VARCHAR(50) NOT NULL DEFAULT 'category',
    color_hex VARCHAR(7) NOT NULL DEFAULT '#6B7280',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- BUDGETS
-- =====================================================
CREATE TABLE budgets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    month INTEGER NOT NULL CHECK (month >= 1 AND month <= 12),
    year INTEGER NOT NULL CHECK (year >= 2000 AND year <= 2100),
    amount_limit DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, month, year)
);

-- =====================================================
-- RECEIPTS
-- =====================================================
CREATE TABLE receipts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    store_name VARCHAR(255) NOT NULL,
    receipt_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    receipt_image_path VARCHAR(500),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- RECEIPT_ITEMS
-- =====================================================
CREATE TABLE receipt_items (
    id SERIAL PRIMARY KEY,
    receipt_id INTEGER NOT NULL REFERENCES receipts(id) ON DELETE CASCADE,
    product_name VARCHAR(255) NOT NULL,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- INDEKSY - przyśpieszają query. Tutaj niezbyt zauważalne, ale przy większej ilości danych pomaga. Domyślnie primary key i unique mają indeksy.
-- =====================================================
CREATE INDEX idx_receipts_user_id ON receipts(user_id);
CREATE INDEX idx_receipts_date ON receipts(receipt_date);
CREATE INDEX idx_receipt_items_receipt_id ON receipt_items(receipt_id);
CREATE INDEX idx_budgets_user_month_year ON budgets(user_id, month, year);
CREATE INDEX idx_categories_user_id ON categories(user_id);

-- =====================================================
-- KATEGORIE DOMYŚLNE 
-- =====================================================
-- Kategorie z user_id = NULL, kopiowane dla każdego nowego usera
INSERT INTO categories (user_id, name, icon_name, color_hex, is_default) VALUES
(NULL, 'Jedzenie', 'restaurant', '#EF4444', TRUE),
(NULL, 'Transport', 'directions_car', '#3B82F6', TRUE),
(NULL, 'Zakupy', 'shopping_bag', '#8B5CF6', TRUE),
(NULL, 'Rozrywka', 'movie', '#F59E0B', TRUE),
(NULL, 'Zdrowie', 'medical_services', '#10B981', TRUE),
(NULL, 'Dom', 'home', '#6366F1', TRUE),
(NULL, 'Rachunki', 'receipt_long', '#EC4899', TRUE),
(NULL, 'Inne', 'more_horiz', '#6B7280', TRUE);

-- =====================================================
-- UŻYTKOWNIK TESTOWY (Hasło: test123)
-- =====================================================
INSERT INTO users (email, password_hash, name, surname) VALUES
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jan', 'Kowalski');

-- Skopiowanie kategorii domyślnych dla testera
INSERT INTO categories (user_id, name, icon_name, color_hex, is_default)
SELECT 1, name, icon_name, color_hex, FALSE
FROM categories
WHERE is_default = TRUE;

-- =====================================================
-- DANE PRZYKŁADOWE - insert
-- =====================================================

-- Przykładowe budżety
INSERT INTO budgets (user_id, month, year, amount_limit) VALUES
(1, EXTRACT(MONTH FROM CURRENT_DATE)::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER, 3000.00),
(1, EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month')::INTEGER, 2800.00),
(1, EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months')::INTEGER, EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '2 months')::INTEGER, 2500.00);

-- Przykładowe paragony
INSERT INTO receipts (user_id, store_name, receipt_date, total_amount, notes) VALUES
(1, 'Biedronka', CURRENT_DATE, 156.45, 'Zakupy tygodniowe'),
(1, 'Żabka', CURRENT_DATE - INTERVAL '1 day', 32.50, 'Przekąski'),
(1, 'Lidl', CURRENT_DATE - INTERVAL '2 days', 245.80, 'Duże zakupy'),
(1, 'Orlen', CURRENT_DATE - INTERVAL '3 days', 280.00, 'Tankowanie'),
(1, 'Rossmann', CURRENT_DATE - INTERVAL '5 days', 89.99, 'Kosmetyki'),
(1, 'Media Expert', CURRENT_DATE - INTERVAL '7 days', 599.00, 'Elektronika'),
(1, 'Allegro', CURRENT_DATE - INTERVAL '10 days', 150.00, 'Zakupy online'),
(1, 'Kino Cinema City', CURRENT_DATE - INTERVAL '12 days', 65.00, 'Bilety do kina'),
(1, 'Apteka', CURRENT_DATE - INTERVAL '15 days', 45.50, 'Leki'),
(1, 'Kaufland', CURRENT_DATE - INTERVAL '20 days', 320.00, 'Zakupy spożywcze');

-- Przykładowe pozycje na paragonach
INSERT INTO receipt_items (receipt_id, product_name, category_id, price, quantity) VALUES
-- Biedronka
(1, 'Chleb', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 5.99, 2),
(1, 'Mleko', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 4.50, 3),
(1, 'Ser żółty', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 12.99, 1),
(1, 'Jabłka', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 8.99, 1),
-- Żabka
(2, 'Kawa', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 12.50, 1),
(2, 'Kanapka', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 15.00, 1),
(2, 'Woda', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Jedzenie' LIMIT 1), 5.00, 1),
-- Orlen
(4, 'Paliwo ON', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Transport' LIMIT 1), 280.00, 1),
-- Media Expert
(6, 'Słuchawki Bluetooth', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Zakupy' LIMIT 1), 599.00, 1),
-- Kino
(8, 'Bilet do kina', (SELECT id FROM categories WHERE user_id = 1 AND name = 'Rozrywka' LIMIT 1), 32.50, 2);

-- =====================================================
-- TRIGGERY I FUNKCJE
-- =====================================================

-- Funkcja: Kopia kategorii domyślnych dla nowego użytkownika
CREATE OR REPLACE FUNCTION clone_default_categories()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO categories (user_id, name, icon_name, color_hex, is_default)
    SELECT NEW.id, name, icon_name, color_hex, FALSE
    FROM categories
    WHERE is_default = TRUE AND user_id IS NULL;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger: Uruchomienie funkcji po dodaniu usera (INSERT ON users)
CREATE TRIGGER trigger_clone_categories
    AFTER INSERT ON users
    FOR EACH ROW
    EXECUTE FUNCTION clone_default_categories();