/**
 * Edit Receipt page JavaScript
 */

let itemCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Load existing items
    if (existingItems.length > 0) {
        existingItems.forEach(item => {
            addItemRow(item.product_name, item.price, item.quantity || 1, item.category_id);
        });
    } else {
        document.getElementById('no-items-msg').classList.remove('hidden');
    }
    
    // Add item button
    document.getElementById('add-item-btn').addEventListener('click', function() {
        addItemRow();
    });
    
    // Form submit handler
    document.getElementById('editForm').addEventListener('submit', handleEditSubmit);
});

/**
 * Add new item row
 */
function addItemRow(name = '', price = '', quantity = 1, categoryId = '') {
    const container = document.getElementById('items-container');
    document.getElementById('no-items-msg').classList.add('hidden');
    
    const itemId = itemCounter++;
    const categoryOptions = categories.map(c => 
        `<option value="${c.id}" ${c.id == categoryId ? 'selected' : ''}>${c.name}</option>`
    ).join('');

    const itemDiv = document.createElement('div');
    itemDiv.className = 'edit-receipt-item-card';
    itemDiv.id = `item-${itemId}`;
    itemDiv.innerHTML = `
        <button type="button" class="edit-receipt-item-remove" data-item-id="${itemId}">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="edit-receipt-item-fields">
            <div class="edit-receipt-item-name-row">
                <input type="text" name="items[${itemId}][name]" placeholder="Nazwa produktu" required
                       value="${escapeHtml(name)}"
                       class="edit-receipt-item-input">
            </div>
            <div class="edit-receipt-item-details-row">
                <select name="items[${itemId}][category_id]" class="edit-receipt-item-select">
                    <option value="">Kategoria</option>
                    ${categoryOptions}
                </select>
                <input type="number" name="items[${itemId}][quantity]" placeholder="Ilość" min="1" value="${quantity}"
                       class="edit-receipt-item-quantity">
                <input type="number" name="items[${itemId}][price]" placeholder="Cena" step="0.01" min="0" required
                       value="${price}"
                       class="edit-receipt-item-price">
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    
    // Add event listeners
    itemDiv.querySelector('.edit-receipt-item-remove').addEventListener('click', function() {
        removeItem(this.dataset.itemId);
    });
    
    itemDiv.querySelector('input[name*="[price]"]').addEventListener('change', calculateTotal);
    itemDiv.querySelector('input[name*="[quantity]"]').addEventListener('change', calculateTotal);
    
    calculateTotal();
}

/**
 * Remove item row
 */
function removeItem(itemId) {
    const item = document.getElementById(`item-${itemId}`);
    if (item) {
        item.remove();
        calculateTotal();
        
        if (document.getElementById('items-container').children.length === 0) {
            document.getElementById('no-items-msg').classList.remove('hidden');
        }
    }
}

/**
 * Calculate total from items
 */
function calculateTotal() {
    const items = document.querySelectorAll('#items-container > div');
    let total = 0;
    
    items.forEach(item => {
        const price = parseFloat(item.querySelector('input[name*="[price]"]').value) || 0;
        const quantity = parseInt(item.querySelector('input[name*="[quantity]"]').value) || 1;
        total += price * quantity;
    });
    
    if (items.length > 0) {
        document.getElementById('total_amount').value = total.toFixed(2);
    }
}

/**
 * Handle form submit
 */
async function handleEditSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/api/receipt/update', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '/receipt?id=' + receiptId;
        } else {
            alert('Błąd: ' + (data.error || 'Nie udało się zapisać zmian'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Wystąpił błąd podczas zapisywania');
    }
}