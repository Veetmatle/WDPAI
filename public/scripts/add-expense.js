/**
 * JS do dodawania wydatku
 */

// `Inicjalizacja, kod czeka aż się cały HTML przemieli i uruchamia poszczególne moduły strony
document.addEventListener('DOMContentLoaded', function() {
    initModeSwitcher();
    initManualForm();
    initOCRUpload();
    initItemsManagement();
});


// Przełączanie trybów - funckja obsługuje zakładki na górze formularza "ręcznie/OCR"
function initModeSwitcher() {
    const modeButtons = document.querySelectorAll('.add-expense-mode-btn');
    const ocrSection = document.getElementById('ocr-section'); // diva znajduje do "OCRa"
    const manualForm = document.getElementById('expenseForm'); // form do dodawania ręcznego
    
    if (!modeButtons.length) return;
    
    // dla każdego przycisku event listenery trzeba dodać, jak któryś będzie kliknięty wykona się kod wewn
    modeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            
            modeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            if (mode === 'manual') {
                if (manualForm) manualForm.style.display = 'flex';
                if (ocrSection) ocrSection.classList.add('hidden');
            } else {
                if (manualForm) manualForm.style.display = 'none';
                if (ocrSection) ocrSection.classList.remove('hidden');
            }
        });
    });
}



// Ręczne wpisywanie wydatku
function initManualForm() {
    // pobieranie formularza wydatków
    const form = document.getElementById('expenseForm');
    if (!form) return;
    
    // wpisuje datę na dzisiaj jeśli pole jest puste
    const dateInput = form.querySelector('input[type="date"]');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Obsługa submit przez Fetch API (jak w edit-receipt.js)
    form.addEventListener('submit', handleAddSubmit);
}

/**
 * Obsługa wysyłania formularza przez Fetch API
 * Wysyła dane do /api/expense/add i obsługuje odpowiedź JSON
 */
async function handleAddSubmit(e) {
    e.preventDefault(); // Stop dla odświeżania, JS obsłuży
    
    const form = e.target;
    const storeName = form.querySelector('input[name="store_name"]');
    const amount = form.querySelector('input[name="total_amount"]');
    
    // Walidacja po stronie klienta
    let isValid = true;
    
    if (storeName && !storeName.value.trim()) {
        showFieldError(storeName, 'Nazwa sklepu jest wymagana');
        isValid = false;
    } else if (storeName) {
        clearFieldError(storeName);
    }
    
    if (amount && (!amount.value || parseFloat(amount.value) <= 0)) {
        showFieldError(amount, 'Podaj prawidłową kwotę');
        isValid = false;
    } else if (amount) {
        clearFieldError(amount);
    }
    
    if (!isValid) {
        return;
    }
    
    // Przygotowanie danych formularza
    const formData = new FormData(form);
    
    // Wysyłka przez Fetch API
    try {
        const response = await fetch('/api/expense/add', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        // Jeśli sukces, przekieruj do podglądu paragonu
        if (data.success) {
            showNotification('Wydatek został dodany!', 'success');
            window.location.replace('/receipt?id=' + data.receipt_id);
        } else {
            showNotification('Błąd: ' + (data.error || 'Nie udało się zapisać'), 'error');
        }
    } catch (error) {
        console.error('Fetch error:', error);
        showNotification('Wystąpił błąd podczas zapisywania', 'error');
    }
}


// OCR - nie działa, do poprawki
function initOCRUpload() {
    const dropZone = document.querySelector('.add-expense-ocr-dropzone');
    const fileInput = document.querySelector('.add-expense-ocr-input');
    const preview = document.querySelector('.add-expense-ocr-preview');
    
    if (!dropZone || !fileInput) return;
    
    // Jak klikniemy w dropzone to otwiera się okno wyboru pliku
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Obsługa drag and drop pliku
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault(); // blokuje przeglądarkę przed odpaleniem przeciągniętego pliku w karcie
        this.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', function() {
        this.classList.remove('dragover'); // jak wyjdzie myszka poza dropzone to usuwa podświetlenie
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover'); // wywal podświetlenie
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]); // wysyła plik przeciągnięty do działania
        }
    });
    
    // To samo co wyżej tylko bez dropa (do wyboru przez okienko)
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        if (!file.type.startsWith('image/')) {
            showNotification('Wybierz plik graficzny', 'error');
            return;
        }
        
        // Podgląd wgrywanego pliku
        if (preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file); // jak przeglądarka przeczyta plik to odpali 
        }
        
        // Zmienia nazwę "wybierz plik" na nazwę pliku dodanego
        dropZone.querySelector('.add-expense-ocr-dropzone-text').textContent = file.name;
    }
}

// Ogarnianie itemków - dodawanie, usuwanie, obliczanie kwoty
function initItemsManagement() {
    const addItemBtn = document.getElementById('add-item-btn');
    const itemsContainer = document.getElementById('items-container');
    const noItemsMsg = document.getElementById('no-items-msg');
    const totalInput = document.getElementById('total_amount');
    
    if (!addItemBtn || !itemsContainer) return;
    
    let itemCount = 0;
    
    addItemBtn.addEventListener('click', function() {
        itemCount++;
        addItemRow(itemCount);
        if (noItemsMsg) noItemsMsg.classList.add('hidden');
    });
    
    // Nasłuchuje kontener itemków jak kliknie w coś w środku to sprawdzam czy to przycisk usuwania
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.add-expense-remove-item-btn')) {
            const row = e.target.closest('.add-expense-item-row');
            if (row) {
                row.remove();
                updateTotal();
                
                // jak nie ma itemków - pokazuje komunikat że brak itemków
                const items = itemsContainer.querySelectorAll('.add-expense-item-row');
                if (items.length === 0 && noItemsMsg) {
                    noItemsMsg.classList.remove('hidden');
                }
            }
        }
    });
    
    // Jeśli zmieni się ilość lub cena to przelicza total
    itemsContainer.addEventListener('input', function(e) {
        if (e.target.matches('input[name*="price"], input[name*="quantity"]')) {
            updateTotal();
        }
    });
    

    // przelicza total
    function updateTotal() {
        const rows = itemsContainer.querySelectorAll('.add-expense-item-row');
        let total = 0;
        
        rows.forEach(function(row) {
            const priceInput = row.querySelector('input[name*="price"]');
            const quantityInput = row.querySelector('input[name*="quantity"]');
            const price = parseFloat(priceInput?.value) || 0;
            const quantity = parseInt(quantityInput?.value) || 1;
            total += price * quantity;
        });
        
        if (totalInput && total > 0) {
            totalInput.value = total.toFixed(2);
        }
    }
}


// Dodawanie rzędu z itemkiem
function addItemRow(index) {
    const container = document.getElementById('items-container'); // kontener na itemki
    if (!container) return;
    
    // Jeśli nie znajdzie poprawnych kategorii to dodaje domyślne, żeby nie było pustej listy i błędu   
    let categoryOptions = '<option value="">Kategoria</option>';
    if (typeof categories !== 'undefined' && Array.isArray(categories)) {
        categories.forEach(function(cat) {
            categoryOptions += `<option value="${cat.id}">${cat.name}</option>`;
        });
    } else {
        categoryOptions += `
            <option value="1">Jedzenie</option>
            <option value="2">Dom</option>
            <option value="3">Zdrowie</option>
            <option value="4">Transport</option>
            <option value="5">Rozrywka</option>
            <option value="6">Inne</option>
        `;
    }
    
    const row = document.createElement('div');
    row.className = 'add-expense-item-row';
    row.innerHTML = `
        <input type="text" name="items[${index}][name]" placeholder="Nazwa produktu" class="add-expense-item-input">
        <input type="number" name="items[${index}][price]" step="0.01" min="0" placeholder="Cena" class="add-expense-item-input">
        <input type="number" name="items[${index}][quantity]" value="1" min="1" placeholder="Ilość" class="add-expense-item-input">
        <select name="items[${index}][category_id]" class="add-expense-item-select">
            ${categoryOptions}
        </select>
        <button type="button" class="add-expense-remove-item-btn" title="Usuń produkt">
            <span class="material-symbols-outlined">close</span>
        </button>
    `;
    
    container.appendChild(row);
}

// Pokazuje błędy przy polach formularza
function showFieldError(input, message) {
    clearFieldError(input);
    
    const wrapper = input.closest('.add-expense-field') || input.parentElement;
    wrapper.classList.add('has-error');
    
    const errorEl = document.createElement('span');
    errorEl.className = 'add-expense-field-error';
    errorEl.textContent = message;
    wrapper.appendChild(errorEl);
}

// Czyści błędy przy polach formularza
function clearFieldError(input) {
    const wrapper = input.closest('.add-expense-field') || input.parentElement;
    wrapper.classList.remove('has-error');
    
    const existingError = wrapper.querySelector('.add-expense-field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Pokazuje powiadomienia na górze ekranu
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}