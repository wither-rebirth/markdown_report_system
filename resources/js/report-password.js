/**
 * Report Password Page JavaScript
 * Handles password form interactions and visibility toggle
 */

// Auto-focus password field on page load
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.focus();
    }
});

/**
 * Toggle password visibility
 * This function can be used to show/hide password text
 */
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const toggleBtn = document.querySelector('.password-toggle');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        if (toggleBtn) {
            toggleBtn.textContent = '🙈';
        }
    } else {
        passwordField.type = 'password';
        if (toggleBtn) {
            toggleBtn.textContent = '👁️';
        }
    }
} 