/**
 * Modal Fix for Laravel Dashboard
 * Prevents modal freezing and provides emergency close functionality
 */

class ModalFix {
    constructor() {
        this.init();
    }

    init() {
        this.bindGlobalEvents();
        this.addEmergencyClose();
        this.fixBackdropIssues();
    }

    bindGlobalEvents() {
        // Prevent multiple modal instances
        $(document).on('show.bs.modal', '.modal', () => {
            this.closeAllModals();
        });

        // Force close on escape key
        $(document).on('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Fix backdrop click
        $(document).on('click', '.modal-backdrop', (e) => {
            this.closeAllModals();
        });

        // Prevent form submission issues
        $(document).on('submit', 'form', (e) => {
            const form = e.target;
            if (form.classList.contains('modal-form')) {
                e.preventDefault();
                this.handleModalFormSubmit(form);
            }
        });
    }

    closeAllModals() {
        $('.modal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('overflow', 'auto');
        $('body').css('padding-right', '0');
    }

    addEmergencyClose() {
        // Create emergency close button
        const emergencyBtn = `
            <div class="position-fixed bottom-0 end-0 m-3" style="z-index: 9999;">
                <button type="button" class="btn btn-danger btn-sm shadow" id="emergencyModalClose">
                    <i class="fas fa-times-circle me-1"></i>
                    Close Modals
                </button>
            </div>
        `;
        
        $('body').append(emergencyBtn);
        
        $('#emergencyModalClose').hide().on('click', () => {
            this.closeAllModals();
        });

        // Show button when modal is open
        $(document).on('show.bs.modal', () => {
            $('#emergencyModalClose').fadeIn();
        });

        $(document).on('hide.bs.modal', () => {
            $('#emergencyModalClose').fadeOut();
        });
    }

    fixBackdropIssues() {
        // Remove duplicate backdrops
        setInterval(() => {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                backdrops.not(':first').remove();
            }
        }, 100);

        // Fix body styles
        setInterval(() => {
            if ($('.modal.show').length === 0) {
                $('body').removeClass('modal-open');
                $('body').css('overflow', 'auto');
                $('body').css('padding-right', '0');
            }
        }, 150);
    }

    handleModalFormSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        submitBtn.disabled = true;

        // Simulate form processing
        setTimeout(() => {
            // Close modal on success
            this.closeAllModals();
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1000);
    }
}

// Initialize when document is ready
$(document).ready(() => {
    window.modalFix = new ModalFix();
    
    // Global modal error handler
    $(document).on('error', '.modal', function(e) {
        console.error('Modal error:', e);
        $(this).modal('hide');
    });
});

// Emergency function for console
window.fixModals = function() {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    console.log('All modals closed forcefully');
};