// Client-specific JavaScript
// ==========================================

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    initializeTooltips();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize form enhancements
    initializeFormEnhancements();
    
    // Initialize common components
    initializeCommonComponents();
});

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize smooth scrolling for anchor links
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Initialize scroll animations
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observe elements with animation class
    const animatedElements = document.querySelectorAll('.card, .feature-card, .alert');
    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

// Initialize form enhancements
function initializeFormEnhancements() {
    // Add loading state to buttons
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Загрузка...';
            }
        });
    });
    
    // Add focus effects to form inputs
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// Utility functions
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// ==========================================
// COMMON COMPONENTS FUNCTIONS
// ==========================================

// Breeds loading functionality
function initializeBreedsLoading() {
    const speciesSelect = document.getElementById('species_id');
    const breedSelect = document.getElementById('breed_id');
    
    if (!speciesSelect || !breedSelect) return;
    
    // Load breeds when species changes
    speciesSelect.addEventListener('change', function() {
        const speciesId = this.value;
        breedSelect.innerHTML = '<option value="">Загрузка...</option>';
        
        if (speciesId) {
            fetch(`/api/breeds-by-species?species_id=${speciesId}`)
                .then(response => response.json())
                .then(breeds => {
                    breedSelect.innerHTML = '<option value="">Выберите породу</option>';
                    breeds.forEach(breed => {
                        const option = document.createElement('option');
                        option.value = breed.id;
                        option.textContent = breed.name;
                        breedSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Ошибка загрузки пород:', error);
                    breedSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                });
        } else {
            breedSelect.innerHTML = '<option value="">Сначала выберите вид</option>';
        }
    });
    
    // Restore selected breed on validation error
    const oldBreedId = breedSelect.dataset.oldBreedId;
    const oldSpeciesId = speciesSelect.dataset.oldSpeciesId;
    
    if (oldBreedId && oldSpeciesId) {
        speciesSelect.value = oldSpeciesId;
        speciesSelect.dispatchEvent(new Event('change'));
        
        // Wait for breeds to load and select old breed
        setTimeout(() => {
            breedSelect.value = oldBreedId;
        }, 500);
    }
}

// Schedule selection functionality
function initializeScheduleSelection() {
    const scheduleCards = document.querySelectorAll('.schedule-card');
    
    scheduleCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selection from other cards
            scheduleCards.forEach(c => c.classList.remove('selected'));
            
            // Select current card
            this.classList.add('selected');
            
            // Get schedule ID
            const scheduleId = this.dataset.scheduleId;
            
            // Redirect to confirmation page
            const confirmUrl = this.dataset.confirmUrl;
            if (confirmUrl) {
                const url = new URL(confirmUrl, window.location.origin);
                url.searchParams.set('schedule_id', scheduleId);
                
                // Clean time from extra characters
                const timeText = this.querySelector('.schedule-time').textContent.trim();
                const cleanTime = timeText.split(' - ')[0].trim();
                url.searchParams.set('time', cleanTime);
                
                window.location.href = url.toString();
            }
        });
    });
}

// Form validation enhancements
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Пожалуйста, исправьте ошибки в форме', 'danger');
            }
        });
    });
}

// Field validation helper
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    const isValid = !isRequired || value.length > 0;
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// AJAX form submission
function initializeAjaxForms() {
    const ajaxForms = document.querySelectorAll('form[data-ajax]');
    
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Загрузка...';
            
            fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Операция выполнена успешно', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    showAlert(data.message || 'Произошла ошибка', 'danger');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showAlert('Произошла ошибка при выполнении запроса', 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
}

// Initialize all common components
function initializeCommonComponents() {
    initializeBreedsLoading();
    initializeScheduleSelection();
    initializeFormValidation();
    initializeAjaxForms();
}

// Export functions for global use
window.ClientUtils = {
    showAlert,
    createAlertContainer,
    initializeBreedsLoading,
    initializeScheduleSelection,
    initializeFormValidation,
    initializeAjaxForms,
    validateField
};
