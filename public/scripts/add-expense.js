document.addEventListener('DOMContentLoaded', function() {
    initModeSwitcher();
    initManualForm();
    initOCRUpload();
    initItemsManagement();
});


function initModeSwitcher() {
    const modeButtons = document.querySelectorAll('.add-expense-mode-btn');
    const ocrSection = document.getElementById('ocr-section');
    const manualForm = document.getElementById('expenseForm');
    
    if (!modeButtons.length) return;
    
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



function initManualForm() {
    const form = document.getElementById('expenseForm');
    if (!form) return;
    
    const dateInput = form.querySelector('input[type="date"]');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    form.addEventListener('submit', handleAddSubmit);
}

async function handleAddSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const storeName = form.querySelector('input[name="store_name"]');
    const amount = form.querySelector('input[name="total_amount"]');
    
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
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/expense/add', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
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


function initOCRUpload() {
    const dropZone = document.querySelector('.add-expense-ocr-dropzone');
    const fileInput = document.querySelector('.add-expense-ocr-input');
    const preview = document.querySelector('.add-expense-ocr-preview');
    
    if (!dropZone || !fileInput) return;
    
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
    
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
        
        if (preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file); 
        }
        
        dropZone.querySelector('.add-expense-ocr-dropzone-text').textContent = file.name;
    }
}

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
    
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.add-expense-remove-item-btn')) {
            const row = e.target.closest('.add-expense-item-row');
            if (row) {
                row.remove();
                updateTotal();
                
                const items = itemsContainer.querySelectorAll('.add-expense-item-row');
                if (items.length === 0 && noItemsMsg) {
                    noItemsMsg.classList.remove('hidden');
                }
            }
        }
    });
    
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
        
        if (totalInput && total > 0) {
            totalInput.value = total.toFixed(2);
        }
    }
}


function addItemRow(index) {
    const container = document.getElementById('items-container');
    if (!container) return;
    
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