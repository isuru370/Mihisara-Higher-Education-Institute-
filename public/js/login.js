// resources/js/login.js

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loginButton = document.querySelector('.btn-login');
    
    // Toggle password visibility
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Show password');
            }
            
            // Add subtle animation
            this.style.transform = 'translateY(-50%) scale(1.1)';
            setTimeout(() => {
                this.style.transform = 'translateY(-50%) scale(1)';
            }, 200);
        });
    }

    // Add focus effects to form inputs
    const inputs = document.querySelectorAll('.login-form-control');
    inputs.forEach(input => {
        const parentGroup = input.closest('.login-input-group');
        
        input.addEventListener('focus', function() {
            if (parentGroup) {
                parentGroup.classList.add('focused');
            }
        });
        
        input.addEventListener('blur', function() {
            if (parentGroup) {
                parentGroup.classList.remove('focused');
            }
        });
        
        // Real-time validation
        input.addEventListener('input', function() {
            validateField(this);
        });
    });

    // Form submission handler
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form before submission
            if (validateForm()) {
                showLoadingState();
                
                // Simulate API call delay (remove in production)
                setTimeout(() => {
                    // In a real application, you would submit the form here
                    // For demo purposes, we'll simulate success after validation
                    this.submit();
                }, 1500);
            } else {
                // Add shake animation to invalid fields
                const invalidFields = loginForm.querySelectorAll('.is-invalid');
                invalidFields.forEach(field => {
                    field.classList.add('shake');
                    setTimeout(() => {
                        field.classList.remove('shake');
                    }, 500);
                });
            }
        });
    }

    // Field validation functions
    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        
        // Remove previous validation classes
        field.classList.remove('is-valid', 'is-invalid');
        
        // Email validation
        if (fieldName === 'email') {
            if (!value) {
                showFieldError(field, 'Email is required');
            } else if (!isValidEmail(value)) {
                showFieldError(field, 'Please enter a valid email address');
            } else {
                showFieldSuccess(field);
            }
        }
        
        // Password validation
        if (fieldName === 'password') {
            if (!value) {
                showFieldError(field, 'Password is required');
            } else if (value.length < 6) {
                showFieldError(field, 'Password must be at least 6 characters');
            } else {
                showFieldSuccess(field);
            }
        }
    }

    function validateForm() {
        let isValid = true;
        
        // Validate email
        if (!emailInput.value.trim()) {
            showFieldError(emailInput, 'Email is required');
            isValid = false;
        } else if (!isValidEmail(emailInput.value.trim())) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate password
        if (!passwordInput.value) {
            showFieldError(passwordInput, 'Password is required');
            isValid = false;
        } else if (passwordInput.value.length < 6) {
            showFieldError(passwordInput, 'Password must be at least 6 characters');
            isValid = false;
        }
        
        return isValid;
    }

    function showFieldError(field, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        // Remove existing feedback
        const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // Add new feedback
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        field.parentNode.appendChild(feedback);
    }

    function showFieldSuccess(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        // Remove existing feedback
        const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function showLoadingState() {
        if (loadingOverlay) {
            loadingOverlay.classList.add('active');
        }
        
        if (loginButton) {
            loginButton.disabled = true;
            loginButton.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Logging in...';
        }
    }

    // Hide loading state when page loads (in case of back navigation)
    function hideLoadingState() {
        if (loadingOverlay) {
            loadingOverlay.classList.remove('active');
        }
        
        if (loginButton) {
            loginButton.disabled = false;
            loginButton.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
        }
    }

    // Initialize
    hideLoadingState();

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter to submit form
        if (e.ctrlKey && e.key === 'Enter') {
            if (loginForm) {
                loginForm.dispatchEvent(new Event('submit'));
            }
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            if (loginForm) {
                loginForm.reset();
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                    const feedback = input.parentNode.querySelector('.invalid-feedback');
                    if (feedback) feedback.remove();
                });
            }
        }
    });

    // Add input animations
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });

    // Pre-fill demo credentials for testing (remove in production)
    if (window.location.href.includes('demo=true')) {
        emailInput.value = 'demo@successacademy.com';
        passwordInput.value = 'demo123';
        
        // Trigger validation
        validateField(emailInput);
        validateField(passwordInput);
    }
});