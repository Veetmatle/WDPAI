let csrfToken = '';

document.addEventListener('DOMContentLoaded', function() {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    }

    initFilters();
});

function setCsrfToken(token) {
    csrfToken = token;
}

async function changeRole(userId, roleId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('role_id', roleId);
    formData.append('csrf_token', csrfToken);

    try {
        const response = await fetch('/admin/set-role', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Błąd: ' + data.error);
            location.reload();
        }
    } catch (error) {
        alert('Wystąpił błąd');
        location.reload();
    }
}

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

function initFilters() {
    const filterName = document.getElementById('filterName');
    const filterRole = document.getElementById('filterRole');
    const filterReset = document.getElementById('filterReset');
    const tableBody = document.querySelector('.admin-table tbody');
    
    if (!filterName || !filterRole || !tableBody) return;
    
    const rows = tableBody.querySelectorAll('tr:not(.no-results-row)');

    function applyFilters() {
        const nameQuery = filterName.value.toLowerCase().trim();
        const roleQuery = filterRole.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const userName = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
            const userEmail = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
            const roleSelect = row.querySelector('.role-select');
            const userRole = roleSelect ? roleSelect.value : '';

            const matchesName = !nameQuery || userName.includes(nameQuery) || userEmail.includes(nameQuery);
            const matchesRole = !roleQuery || userRole === roleQuery;

            if (matchesName && matchesRole) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        let noResults = tableBody.querySelector('.no-results-row');
        if (visibleCount === 0) {
            if (!noResults) {
                noResults = document.createElement('tr');
                noResults.className = 'no-results-row';
                noResults.innerHTML = '<td colspan="6" class="admin-no-results"><span class="material-symbols-outlined">search_off</span>Brak wyników dla podanych filtrów</td>';
                tableBody.appendChild(noResults);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }

    filterName.addEventListener('input', applyFilters);
    filterRole.addEventListener('change', applyFilters);
    
    if (filterReset) {
        filterReset.addEventListener('click', () => {
            filterName.value = '';
            filterRole.value = '';
            applyFilters();
        });
    }
}
