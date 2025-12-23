/**
 * Add Expense Page JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initModeSwitcher();
    initManualForm();
    initOCRUpload();
    initItemsManagement();
});

// Mode switching (Manual vs OCR)
function initModeSwitcher() {
    const modeButtons = document.querySelectorAll('.add-expense-mode-btn');
    const ocrSection = document.getElementById('ocr-section');
    const manualForm = document.getElementById('expenseForm');
    
    if (!modeButtons.length) return;
    
    modeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            
            // Update active button
            modeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide sections
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

// Manual form handling
function initManualForm() {
    const form = document.getElementById('expenseForm');
    if (!form) return;
    
    // Date default to today
    const dateInput = form.querySelector('input[type="date"]');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const storeName = form.querySelector('input[name="store_name"]');
        const amount = form.querySelector('input[name="total_amount"]');
        
        let isValid = true;
        
        if (storeName && !storeName.value.trim()) {
            showFieldError(storeName, 'Nazwa sklepu jest wymagana');
            isValid = false;
        }
        
        if (amount && (!amount.value || parseFloat(amount.value) <= 0)) {
            showFieldError(amount, 'Podaj prawidłową kwotę');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// OCR upload handling
function initOCRUpload() {
    const dropZone = document.querySelector('.add-expense-ocr-dropzone');
    const fileInput = document.querySelector('.add-expense-ocr-input');
    const preview = document.querySelector('.add-expense-ocr-preview');
    
    if (!dropZone || !fileInput) return;
    
    // Click to upload
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    // File input change
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
        
        // Show preview
        if (preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        // Update dropzone text
        dropZone.querySelector('.add-expense-ocr-dropzone-text').textContent = file.name;
    }
}

// Items management (add/remove items)
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
    
    // Delegate remove button clicks
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.add-expense-remove-item-btn')) {
            const row = e.target.closest('.add-expense-item-row');
            if (row) {
                row.remove();
                updateTotal();
                
                // Show no items message if no items left
                const items = itemsContainer.querySelectorAll('.add-expense-item-row');
                if (items.length === 0 && noItemsMsg) {
                    noItemsMsg.classList.remove('hidden');
                }
            }
        }
    });
    
    // Update total on input change
    itemsContainer.addEventListener('input', function(e) {
        if (e.target.matches('input[name*="price"], input[name*="quantity"]')) {
            updateTotal();
        }
    });
    
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
        
        // Update total input field
        if (totalInput && total > 0) {
            totalInput.value = total.toFixed(2);
        }
    }
}

function addItemRow(index) {
    const container = document.getElementById('items-container');
    if (!container) return;
    
    // Build category options from global categories variable
    let categoryOptions = '<option value="">Kategoria</option>';
    if (typeof categories !== 'undefined' && Array.isArray(categories)) {
        categories.forEach(function(cat) {
            categoryOptions += `<option value="${cat.id}">${cat.name}</option>`;
        });
    } else {
        // Fallback categories
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

function showFieldError(input, message) {
    clearFieldError(input);
    
    const wrapper = input.closest('.add-expense-field') || input.parentElement;
    wrapper.classList.add('has-error');
    
    const errorEl = document.createElement('span');
    errorEl.className = 'add-expense-field-error';
    errorEl.textContent = message;
    wrapper.appendChild(errorEl);
}

function clearFieldError(input) {
    const wrapper = input.closest('.add-expense-field') || input.parentElement;
    wrapper.classList.remove('has-error');
    
    const existingError = wrapper.querySelector('.add-expense-field-error');
    if (existingError) {
        existingError.remove();
    }
}

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