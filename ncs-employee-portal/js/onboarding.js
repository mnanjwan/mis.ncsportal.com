// Onboarding Form Handler
class OnboardingManager {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.formData = {
            step1: {},
            step2: {},
            step3: {},
            step4: {}
        };
        this.init();
    }

    init() {
        // Load saved data from localStorage
        this.loadSavedData();
        
        // Initialize step navigation
        this.setupStepNavigation();
        
        // Initialize form validation
        this.setupValidation();
    }

    loadSavedData() {
        const saved = localStorage.getItem('onboarding_data');
        if (saved) {
            this.formData = JSON.parse(saved);
            this.populateForms();
        }
    }

    saveData() {
        localStorage.setItem('onboarding_data', JSON.stringify(this.formData));
    }

    populateForms() {
        // Populate step 1
        if (this.formData.step1) {
            Object.keys(this.formData.step1).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) field.value = this.formData.step1[key];
            });
        }

        // Populate step 2
        if (this.formData.step2) {
            Object.keys(this.formData.step2).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) field.value = this.formData.step2[key];
            });
        }

        // Populate step 3
        if (this.formData.step3) {
            Object.keys(this.formData.step3).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) field.value = this.formData.step3[key];
            });
        }

        // Populate step 4
        if (this.formData.step4 && this.formData.step4.next_of_kin) {
            this.populateNextOfKin(this.formData.step4.next_of_kin);
        }
    }

    populateNextOfKin(kinData) {
        const container = document.getElementById('next-of-kin-container');
        if (!container) return;

        container.innerHTML = '';
        kinData.forEach((kin, index) => {
            this.addNextOfKinField(kin, index);
        });
    }

    setupStepNavigation() {
        // Next button
        const nextBtn = document.getElementById('btn-next');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextStep());
        }

        // Previous button
        const prevBtn = document.getElementById('btn-prev');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prevStep());
        }

        // Submit button
        const submitBtn = document.getElementById('btn-submit');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.submitOnboarding());
        }
    }

    setupValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (this.validateCurrentStep()) {
                    this.saveCurrentStep();
                    if (this.currentStep < this.totalSteps) {
                        this.nextStep();
                    } else {
                        this.submitOnboarding();
                    }
                }
            });
        });
    }

    validateCurrentStep() {
        const step = this.currentStep;
        const form = document.querySelector(`form[data-step="${step}"]`) || document.querySelector('form');
        
        if (!form) return true;

        // Get all required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('border-danger');
                this.showError(field, 'This field is required');
            } else {
                field.classList.remove('border-danger');
                this.hideError(field);
            }

            // Email validation
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    field.classList.add('border-danger');
                    this.showError(field, 'Please enter a valid email address');
                }
            }

            // RSA PIN validation
            if (field.name === 'rsa_number' && field.value) {
                const rsaRegex = /^PEN\d{12}$/;
                if (!rsaRegex.test(field.value)) {
                    isValid = false;
                    field.classList.add('border-danger');
                    this.showError(field, 'RSA PIN must be in format PEN followed by 12 digits');
                }
            }
        });

        return isValid;
    }

    showError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-danger text-sm mt-1';
        errorDiv.id = `error-${field.name}`;
        errorDiv.textContent = message;
        
        const existingError = document.getElementById(`error-${field.name}`);
        if (existingError) {
            existingError.remove();
        }
        
        field.parentElement.appendChild(errorDiv);
    }

    hideError(field) {
        const errorDiv = document.getElementById(`error-${field.name}`);
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    saveCurrentStep() {
        const step = this.currentStep;
        const form = document.querySelector(`form[data-step="${step}"]`) || document.querySelector('form');
        
        if (!form) return;

        const formData = new FormData(form);
        const data = {};
        
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Special handling for step 4 (next of kin)
        if (step === 4) {
            data.next_of_kin = this.getNextOfKinData();
        }

        this.formData[`step${step}`] = data;
        this.saveData();
    }

    getNextOfKinData() {
        const container = document.getElementById('next-of-kin-container');
        if (!container) return [];

        const kinItems = container.querySelectorAll('.next-of-kin-item');
        const kinData = [];

        kinItems.forEach(item => {
            kinData.push({
                name: item.querySelector('[name="kin_name[]"]')?.value || '',
                relationship: item.querySelector('[name="kin_relationship[]"]')?.value || '',
                phone_number: item.querySelector('[name="kin_phone[]"]')?.value || '',
                address: item.querySelector('[name="kin_address[]"]')?.value || ''
            });
        });

        return kinData;
    }

    nextStep() {
        if (this.validateCurrentStep()) {
            this.saveCurrentStep();
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                window.location.href = `step${this.currentStep}-${this.getStepName(this.currentStep)}.html`;
            }
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.saveCurrentStep();
            this.currentStep--;
            window.location.href = `step${this.currentStep}-${this.getStepName(this.currentStep)}.html`;
        }
    }

    getStepName(step) {
        const names = {
            1: 'personal',
            2: 'employment',
            3: 'banking',
            4: 'next-of-kin'
        };
        return names[step] || 'personal';
    }

    addNextOfKinField(data = {}) {
        const container = document.getElementById('next-of-kin-container');
        if (!container) return;

        const index = container.children.length;
        const item = document.createElement('div');
        item.className = 'next-of-kin-item kt-card p-4 mb-4';
        item.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-semibold">Next of Kin ${index + 1}</h4>
                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger" onclick="onboardingManager.removeNextOfKinField(this)">
                    <i class="ki-filled ki-cross"></i> Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="kt-form-label required">Full Name</label>
                    <input type="text" name="kin_name[]" class="kt-input" value="${data.name || ''}" required>
                </div>
                <div>
                    <label class="kt-form-label required">Relationship</label>
                    <select name="kin_relationship[]" class="kt-input" required>
                        <option value="">Select Relationship</option>
                        <option value="Spouse" ${data.relationship === 'Spouse' ? 'selected' : ''}>Spouse</option>
                        <option value="Parent" ${data.relationship === 'Parent' ? 'selected' : ''}>Parent</option>
                        <option value="Sibling" ${data.relationship === 'Sibling' ? 'selected' : ''}>Sibling</option>
                        <option value="Child" ${data.relationship === 'Child' ? 'selected' : ''}>Child</option>
                        <option value="Other" ${data.relationship === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="kt-form-label">Phone Number</label>
                    <input type="tel" name="kin_phone[]" class="kt-input" value="${data.phone_number || ''}">
                </div>
                <div>
                    <label class="kt-form-label">Address</label>
                    <input type="text" name="kin_address[]" class="kt-input" value="${data.address || ''}">
                </div>
            </div>
        `;
        container.appendChild(item);
    }

    removeNextOfKinField(button) {
        const container = document.getElementById('next-of-kin-container');
        if (container && container.children.length > 1) {
            button.closest('.next-of-kin-item').remove();
        } else {
            alert('At least one next of kin is required');
        }
    }

    async submitOnboarding() {
        if (!this.validateCurrentStep()) {
            return;
        }

        this.saveCurrentStep();

        // Combine all step data
        const submissionData = {
            ...this.formData.step1,
            ...this.formData.step2,
            ...this.formData.step3,
            ...this.formData.step4
        };

        // Format next of kin
        if (submissionData.next_of_kin) {
            submissionData.next_of_kin = submissionData.next_of_kin.map(kin => ({
                name: kin.name,
                relationship: kin.relationship,
                phone_number: kin.phone_number || null,
                address: kin.address || null
            }));
        }

        try {
            const submitBtn = document.getElementById('btn-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Submitting...';
            }

            // Submit to API
            const response = await apiService.post(API_CONFIG.endpoints.officers.onboarding, submissionData);

            // Clear saved data
            localStorage.removeItem('onboarding_data');

            // Show success message
            alert('Onboarding completed successfully!');
            
            // Redirect to dashboard
            window.location.href = '../../dashboards/officer/dashboard.html';
        } catch (error) {
            console.error('Onboarding error:', error);
            alert('Error submitting onboarding: ' + error.message);
            
            const submitBtn = document.getElementById('btn-submit');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit';
            }
        }
    }
}

// Initialize on page load
let onboardingManager;
document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname;
    if (currentPath.includes('onboarding')) {
        onboardingManager = new OnboardingManager();
        
        // Add next of kin button
        const addKinBtn = document.getElementById('btn-add-kin');
        if (addKinBtn) {
            addKinBtn.addEventListener('click', () => onboardingManager.addNextOfKinField());
        }

        // Initialize with at least one next of kin
        const container = document.getElementById('next-of-kin-container');
        if (container && container.children.length === 0) {
            onboardingManager.addNextOfKinField();
        }
    }
});

