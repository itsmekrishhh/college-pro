/**
 * Bella Italia - Main JavaScript File
 * Handles cart updates, form validation, and UI interactions
 */

// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

/**
 * Update cart count in navigation
 */
function updateCartCount() {
    // This would typically fetch from the server
    // For now, we'll use session storage or count from the page
    const cartCountElements = document.querySelectorAll('.cart-count');
    if (cartCountElements.length > 0) {
        // Get cart count from cart items if on cart page
        const cartItems = document.querySelectorAll('.cart-item');
        if (cartItems.length > 0) {
            cartCountElements.forEach(el => {
                el.textContent = cartItems.length;
            });
        } else {
            // Try to get from session storage or default to 0
            const cartCount = sessionStorage.getItem('cartCount') || '0';
            cartCountElements.forEach(el => {
                el.textContent = cartCount;
            });
        }
    }
}

/**
 * Update cart item quantity
 */
function updateCartQuantity(productId, quantity) {
    // This would typically make an AJAX call to update the cart
    // For now, it's handled by form submission in cart.php
    const form = document.querySelector(`form[data-product-id="${productId}"]`);
    if (form) {
        const quantityInput = form.querySelector('input[name="quantity"]');
        if (quantityInput) {
            quantityInput.value = quantity;
            form.submit();
        }
    }
}

/**
 * Remove cart item
 */
function removeCartItem(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/college-pro/cart/cart.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'remove';
        
        const productInput = document.createElement('input');
        productInput.type = 'hidden';
        productInput.name = 'product_id';
        productInput.value = productId;
        
        form.appendChild(actionInput);
        form.appendChild(productInput);
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Basic form validation
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#d32f2f';
        } else {
            field.style.borderColor = '';
        }
    });
    
    // Validate email fields
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (field.value && !emailPattern.test(field.value)) {
            isValid = false;
            field.style.borderColor = '#d32f2f';
        }
    });
    
    // Validate password confirmation
    const passwordField = form.querySelector('input[name="password"]');
    const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
    if (passwordField && confirmPasswordField) {
        if (passwordField.value !== confirmPasswordField.value) {
            isValid = false;
            confirmPasswordField.style.borderColor = '#d32f2f';
            alert('Passwords do not match.');
        }
    }
    
    return isValid;
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Initialize quantity controls
 */
document.addEventListener('DOMContentLoaded', function() {
    // Quantity increase/decrease buttons
    document.querySelectorAll('.quantity-control button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('input[type="number"]');
            if (input) {
                if (this.textContent === '+') {
                    if (parseInt(input.value) < parseInt(input.max || 10)) {
                        input.value = parseInt(input.value) + 1;
                        input.dispatchEvent(new Event('change'));
                    }
                } else if (this.textContent === '-') {
                    if (parseInt(input.value) > parseInt(input.min || 1)) {
                        input.value = parseInt(input.value) - 1;
                        input.dispatchEvent(new Event('change'));
                    }
                }
            }
        });
    });
});

/**
 * Handle tab switching
 */
function switchTab(tabName) {
    // Remove active class from all tabs and tab contents
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Add active class to selected tab and content
    const selectedTab = document.querySelector(`.tab[data-tab="${tabName}"]`);
    const selectedContent = document.querySelector(`.tab-content[data-tab="${tabName}"]`);
    
    if (selectedTab) selectedTab.classList.add('active');
    if (selectedContent) selectedContent.classList.add('active');
}

// Initialize any page-specific functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Bella Italia - Page Loaded');
});

