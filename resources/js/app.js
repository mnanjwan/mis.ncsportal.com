import './bootstrap';
import Swal from 'sweetalert2';
import './session-timeout';
import { createSearchableSelect } from './searchable-select';

// Expose globally for Blade templates that use custom selects
window.createSearchableSelect = createSearchableSelect;

// Configure SweetAlert2 with site colors
window.Swal = Swal.mixin({
    customClass: {
        confirmButton: 'swal2-confirm-btn',
        cancelButton: 'swal2-cancel-btn',
        actions: 'swal2-actions-spaced',
        popup: 'swal2-custom-popup'
    },
    buttonsStyling: false,
    confirmButtonColor: '#088a56',
    cancelButtonColor: '#6c757d',
});

// Add custom styles for SweetAlert2
const style = document.createElement('style');
style.textContent = `
    .swal2-custom-popup {
        font-family: inherit;
    }
    .swal2-popup .swal2-title {
        color: #1e293b;
        font-weight: 600;
    }
    .swal2-popup .swal2-html-container {
        color: #64748b;
    }
    
    /* Button spacing */
    .swal2-actions-spaced {
        gap: 0.75rem !important;
        padding: 1rem 0 !important;
    }
    
    /* Confirm button (Green) */
    .swal2-confirm-btn {
        background-color: #088a56 !important;
        border: 1px solid #088a56 !important;
        color: white !important;
        padding: 0.625rem 1.5rem !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s !important;
        min-width: 100px !important;
    }
    .swal2-confirm-btn:hover {
        background-color: #076d45 !important;
        border-color: #076d45 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(8, 138, 86, 0.2);
    }
    .swal2-confirm-btn:focus {
        box-shadow: 0 0 0 3px rgba(8, 138, 86, 0.3) !important;
    }
    
    /* Cancel button (Gray) */
    .swal2-cancel-btn {
        background-color: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        padding: 0.625rem 1.5rem !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s !important;
        min-width: 100px !important;
    }
    .swal2-cancel-btn:hover {
        background-color: #e2e8f0 !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .swal2-cancel-btn:focus {
        box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.3) !important;
    }
    
    /* Success icon */
    .swal2-icon.swal2-success [class^='swal2-success-line'] {
        background-color: #088a56 !important;
    }
    .swal2-icon.swal2-success .swal2-success-ring {
        border-color: rgba(8, 138, 86, 0.3) !important;
    }
    
    /* Error icon */
    .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
        background-color: #ef4444 !important;
    }
`;
document.head.appendChild(style);

