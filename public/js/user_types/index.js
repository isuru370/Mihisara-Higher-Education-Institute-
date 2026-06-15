// User Types Management - JavaScript
let userTypes = [];
let filteredTypes = [];
let currentTypeId = null;
let editModalInstance = null;
let searchDebounceTimer = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing User Types page...');
    
    // Initialize modals - FIX: Check if elements exist first
    const editModalElement = document.getElementById('editTypeModal');
    
    if (editModalElement) {
        editModalInstance = new bootstrap.Modal(editModalElement, {
            backdrop: true, // Changed from static to true
            keyboard: true  // Allow escape key to close
        });
        
        // FIX: Add proper event listeners for modal
        editModalElement.addEventListener('hidden.bs.modal', function() {
            resetEditForm();
        });
        
    } else {
        console.error('Edit modal element not found');
    }

    // Event listeners - FIX: Check if elements exist
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const updateTypeBtn = document.getElementById('updateTypeBtn');

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.value = '';
                applySearch('');
            }
        });
    }

    if (updateTypeBtn) {
        updateTypeBtn.addEventListener('click', function() {
            console.log('Update button clicked');
            updateUserType();
        });
    }

    // Load user types
    loadUserTypes();
    initializeSearch();
    
    console.log('User Types page initialized successfully');
});

/* ----------------------- Modal Functions ----------------------- */
// FIX: Add missing closeEditModal function
function closeEditModal() {
    console.log('Closing edit modal...');
    if (editModalInstance) {
        editModalInstance.hide();
    }
    resetEditForm();
}

// FIX: Add function to reset edit form
function resetEditForm() {
    const editTypeId = document.getElementById('editTypeId');
    const editTypeName = document.getElementById('editTypeName');
    const typeNameError = document.getElementById('typeNameError');
    
    if (editTypeId) editTypeId.value = '';
    if (editTypeName) {
        editTypeName.value = '';
        editTypeName.classList.remove('is-invalid');
    }
    if (typeNameError) typeNameError.classList.remove('d-block');
    
    // Reset update button
    const updateBtn = document.getElementById('updateTypeBtn');
    if (updateBtn) {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Type';
    }
}

/* ----------------------- Load User Types ----------------------- */
function loadUserTypes() {
    showLoading();
    hideError();

    console.log('Loading user types from /user-types/list');

    fetch('/user-types/list', {
        method: 'GET',
        headers: { 
            'Accept': 'application/json', 
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('User types data received:', data);
        userTypes = Array.isArray(data) ? data : (data.data || []);
        filteredTypes = [...userTypes];
        renderTypesTable(filteredTypes);
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showError('Failed to load user types: ' + error.message);
        showEmpty(true);
    })
    .finally(() => {
        hideLoading();
        console.log('User types loading completed');
    });
}

/* ----------------------- Render Table ----------------------- */
function renderTypesTable(types) {
    const tbody = document.getElementById('typesTableBody');
    const tableContainer = document.getElementById('typesTableContainer');
    const actionBar = document.getElementById('actionBar');
    const emptyState = document.getElementById('emptyState');

    // FIX: Check if elements exist
    if (!tbody || !tableContainer || !actionBar || !emptyState) {
        console.error('Required table elements not found');
        return;
    }

    tbody.innerHTML = '';

    if (!types || types.length === 0) {
        tableContainer.classList.add('d-none');
        actionBar.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }

    tableContainer.classList.remove('d-none');
    actionBar.classList.remove('d-none');
    emptyState.classList.add('d-none');

    // Update statistics
    updateStatistics(types);

    types.forEach((type, index) => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div class="fw-semibold text-dark">${escapeHtml(type.type)}</div>
            </td>
            <td>
                <small class="text-muted">${formatDate(type.created_at)}</small>
            </td>
            <td>
                <small class="text-muted">${formatDate(type.updated_at)}</small>
            </td>
            <td>
                <div class="action-buttons d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" onclick="editUserType(${type.id})" title="Edit Type">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    initializeTooltips();
    console.log('Table rendered with', types.length, 'user types');
}

/* ----------------------- User Type Actions ----------------------- */
function editUserType(typeId) {
    console.log('Editing user type:', typeId);
    
    const type = userTypes.find(t => t.id === typeId);
    if (!type) {
        console.error('User type not found:', typeId);
        showNotification('User type not found', 'error');
        return;
    }

    // FIX: Check if modal elements exist
    const editTypeId = document.getElementById('editTypeId');
    const editTypeName = document.getElementById('editTypeName');
    
    if (!editTypeId || !editTypeName) {
        console.error('Edit modal form elements not found');
        showNotification('Edit form not available', 'error');
        return;
    }

    editTypeId.value = type.id;
    editTypeName.value = type.type;
    
    // FIX: Check if modal instance exists
    if (editModalInstance) {
        editModalInstance.show();
        console.log('Edit modal shown for type:', type.type);
    } else {
        console.error('Edit modal instance not available');
        showNotification('Edit modal not available', 'error');
    }
}

function updateUserType() {
    console.log('Updating user type...');
    
    const typeId = document.getElementById('editTypeId')?.value;
    const typeName = document.getElementById('editTypeName')?.value?.trim();

    if (!typeId || !typeName) {
        console.error('Missing type ID or name');
        showNotification('Please provide a type name', 'error');
        return;
    }

    const submitBtn = document.getElementById('updateTypeBtn');
    if (!submitBtn) {
        console.error('Update button not found');
        return;
    }

    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

    console.log('Sending update request for type:', typeId, 'with name:', typeName);

    fetch(`/api/user-types/${typeId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ type: typeName })
    })
    .then(async response => {
        console.log('Update response status:', response.status);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}`);
        }
        
        return data;
    })
    .then(data => {
        console.log('Update successful:', data);
        if (editModalInstance) {
            editModalInstance.hide();
        }
        showNotification('User type updated successfully!', 'success');
        loadUserTypes(); // Reload the table
    })
    .catch(error => {
        console.error('Error updating user type:', error);
        showNotification(error.message || 'Failed to update user type', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/* ----------------------- Search & Utilities ----------------------- */
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        console.warn('Search input not found');
        return;
    }
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => applySearch(this.value), 300);
    });
}

function applySearch(term) {
    term = (term || '').toString().trim().toLowerCase();
    filteredTypes = term ? userTypes.filter(type =>
        (type.type || '').toLowerCase().includes(term)
    ) : [...userTypes];
    renderTypesTable(filteredTypes);
}

function updateStatistics(types) {
    const totalTypes = types.length;
    const totalTypesElement = document.getElementById('totalTypes');
    const activeTypesElement = document.getElementById('activeTypes');
    const typeCountElement = document.getElementById('typeCount');
    
    if (totalTypesElement) totalTypesElement.textContent = totalTypes;
    if (activeTypesElement) activeTypesElement.textContent = totalTypes;
    if (typeCountElement) typeCountElement.textContent = `Showing ${filteredTypes.length} of ${totalTypes} types`;
}

function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(dateString) {
    try {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return dateString || 'N/A';
    }
}

function showNotification(message, type = 'success') {
    // Simple notification - you can replace with Toast
    console.log(`${type.toUpperCase()}: ${message}`);
    
    // Create a better notification system
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function showLoading() { 
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.classList.remove('d-none'); 
}

function hideLoading() { 
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.classList.add('d-none'); 
}

function showError(msg) {
    const err = document.getElementById('errorMessage');
    if (err) {
        const errorText = document.getElementById('errorText');
        if (errorText) errorText.textContent = msg;
        err.classList.remove('d-none');
    }
}

function hideError() { 
    const err = document.getElementById('errorMessage');
    if (err) err.classList.add('d-none'); 
}

function showEmpty(show = true) {
    const emptyState = document.getElementById('emptyState');
    if (emptyState) emptyState.classList.toggle('d-none', !show);
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.forEach(el => { 
        try { 
            if (el._tooltip) el._tooltip.dispose(); 
        } catch { } 
        new bootstrap.Tooltip(el); 
    });
}

// FIX: Add emergency modal close function
function emergencyModalClose() {
    console.log('Emergency modal close triggered');
    
    // Close all modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });
    
    // Remove any leftover backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });
    
    // Reset body styles
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    
    console.log('Emergency cleanup completed');
}