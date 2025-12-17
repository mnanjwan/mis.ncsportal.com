// Utility functions for the application
const Utils = {
    // Format date
    formatDate(date) {
        if (!date) return 'N/A';
        const d = new Date(date);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },

    // Format datetime
    formatDateTime(date) {
        if (!date) return 'N/A';
        const d = new Date(date);
        return d.toLocaleString('en-GB');
    },

    // Show loading
    showLoading(element) {
        if (element) {
            element.disabled = true;
            element.innerHTML = '<i class="ki-filled ki-loading"></i> Loading...';
        }
    },

    // Hide loading
    hideLoading(element, originalText) {
        if (element) {
            element.disabled = false;
            element.innerHTML = originalText || 'Submit';
        }
    },

    // Show alert
    showAlert(message, type = 'info') {
        // Simple alert - can be enhanced with toast notifications
        alert(message);
    },

    // Show success message
    showSuccess(message) {
        this.showAlert(message, 'success');
    },

    // Show error message
    showError(message) {
        this.showAlert(message, 'error');
    },

    // Get status badge class
    getStatusBadgeClass(status) {
        const statusClasses = {
            'PENDING': 'kt-badge-warning',
            'RAISED': 'kt-badge-info',
            'ASSESSED': 'kt-badge-primary',
            'VALIDATED': 'kt-badge-success',
            'APPROVED': 'kt-badge-success',
            'REJECTED': 'kt-badge-danger',
            'MINUTED': 'kt-badge-info',
            'PROCESSED': 'kt-badge-success',
            'ACTIVE': 'kt-badge-success',
            'INACTIVE': 'kt-badge-secondary'
        };
        return statusClasses[status] || 'kt-badge-secondary';
    },

    // Format status text
    formatStatus(status) {
        return status ? status.replace(/_/g, ' ') : 'N/A';
    },

    // Validate email
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Validate RSA PIN
    validateRsaPin(pin) {
        const re = /^PEN\d{12}$/;
        return re.test(pin);
    },

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

