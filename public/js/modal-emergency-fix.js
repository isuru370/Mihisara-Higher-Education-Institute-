/**
 * EMERGENCY MODAL FIX for User Types
 * This will completely fix the black modal issue
 */

class ModalEmergencyFix {
    constructor() {
        this.init();
    }

    init() {
        this.removeAllBackdrops();
        this.fixModalStyles();
        this.bindGlobalEvents();
        this.createEmergencyControls();
    }

    removeAllBackdrops() {
        // Remove all existing backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
        document.body.style.paddingRight = '0';
    }

    fixModalStyles() {
        // Fix all modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
    }

    bindGlobalEvents() {
        // Global click handler for edit buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('[onclick*="editUserType"]')) {
                e.preventDefault();
                e.stopPropagation();
                setTimeout(() => this.safeEditUserType(e), 10);
            }
        });

        // Escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.forceCloseAllModals();
            }
        });

        // Prevent multiple backdrops
        setInterval(() => this.removeDuplicateBackdrops(), 100);
    }

    safeEditUserType(event) {
        const button = event.target.closest('button');
        const onclick = button.getAttribute('onclick');
        const typeId = onclick.match(/editUserType\((\d+)\)/)[1];
        
        this.forceCloseAllModals();
        
        setTimeout(() => {
            this.openEditModalSafely(parseInt(typeId));
        }, 50);
    }

    openEditModalSafely(typeId) {
        // First close any existing modals
        this.forceCloseAllModals();
        
        // Get the type data
        const type = window.userTypes?.find(t => t.id === typeId);
        if (!type) {
            alert('User type not found');
            return;
        }

        // Set form values
        const editTypeId = document.getElementById('editTypeId');
        const editTypeName = document.getElementById('editTypeName');
        
        if (editTypeId && editTypeName) {
            editTypeId.value = type.id;
            editTypeName.value = type.type;
        }

        // Show modal manually (without Bootstrap)
        const modal = document.getElementById('editTypeModal');
        if (modal) {
            // Create backdrop manually
            this.createBackdrop();
            
            // Show modal
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.style.background = 'rgba(0,0,0,0.5)';
            modal.setAttribute('aria-hidden', 'false');
            
            // Add body classes
            document.body.classList.add('modal-open');
            
            console.log('Modal opened safely for type:', typeId);
        }
    }

    createBackdrop() {
        this.removeAllBackdrops();
        
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: #000;
            opacity: 0.5;
        `;
        
        backdrop.addEventListener('click', () => this.forceCloseAllModals());
        document.body.appendChild(backdrop);
        
        return backdrop;
    }

    removeDuplicateBackdrops() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            for (let i = 1; i < backdrops.length; i++) {
                backdrops[i].remove();
            }
        }
    }

    createEmergencyControls() {
        // Remove existing emergency button
        const existingBtn = document.getElementById('globalEmergencyClose');
        if (existingBtn) existingBtn.remove();

        // Create new emergency button
        const emergencyBtn = document.createElement('button');
        emergencyBtn.id = 'globalEmergencyClose';
        emergencyBtn.innerHTML = '<i class="fas fa-times me-1"></i>Close Modals';
        emergencyBtn.className = 'btn btn-danger btn-sm position-fixed';
        emergencyBtn.style.cssText = `
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
        `;
        
        emergencyBtn.addEventListener('click', () => this.forceCloseAllModals());
        document.body.appendChild(emergencyBtn);

        // Show/hide based on modal state
        setInterval(() => {
            const modal = document.querySelector('.modal.show');
            emergencyBtn.style.display = modal ? 'block' : 'none';
        }, 100);
    }

    forceCloseAllModals() {
        // Close all modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });

        // Remove all backdrops
        this.removeAllBackdrops();

        // Hide emergency button
        const emergencyBtn = document.getElementById('globalEmergencyClose');
        if (emergencyBtn) emergencyBtn.style.display = 'none';

        console.log('All modals force closed');
    }
}

// Initialize immediately
document.addEventListener('DOMContentLoaded', function() {
    window.modalFix = new ModalEmergencyFix();
    
    // Global function for edit buttons
    window.safeEditUserType = function(typeId) {
        window.modalFix.openEditModalSafely(typeId);
    };
    
    console.log('Modal Emergency Fix loaded');
});

// Global emergency function
window.fixAllModals = function() {
    if (window.modalFix) {
        window.modalFix.forceCloseAllModals();
    }
};