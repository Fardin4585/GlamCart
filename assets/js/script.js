/**
 * GlamCart - Makeup and Cosmetics Shop Management System
 * JavaScript functionality for interactive features
 */

// Global variables
let cart = [];
let wishlist = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Load cart and wishlist from localStorage
    loadCartFromStorage();
    loadWishlistFromStorage();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize filters
    initializeFilters();
    
    // Initialize product interactions
    initializeProductInteractions();
    
    // Update cart and wishlist counts
    updateCartCount();
    updateWishlistCount();
}

// Mobile Menu Functionality
function initializeMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }
}

// Search Functionality
function initializeSearch() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    
    console.log('Search form found:', searchForm);
    console.log('Search input found:', searchInput);
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            console.log('Search submitted with query:', query);
            if (query) {
                window.location.href = `shop.php?search=${encodeURIComponent(query)}`;
            }
        });
    }
    
    // Live search (if implemented)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            console.log('Search input value:', this.value);
        });
        
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                performLiveSearch(query);
            }
        }, 300));
    }
}

// Debounce function for search
function debounce(func, wait) {
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

// Live search functionality
function performLiveSearch(query) {
    // This would typically make an AJAX call to search products
    console.log('Searching for:', query);
    // Implementation would depend on backend API
}

// Filter Functionality
function initializeFilters() {
    const filterForm = document.querySelector('.filter-form');
    const filterInputs = document.querySelectorAll('.filter-input');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
    
    // Auto-apply filters on change
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            applyFilters();
        });
    });
}

function applyFilters() {
    const form = document.querySelector('.filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Redirect to shop page with filters
    window.location.href = `shop.php?${params.toString()}`;
}

// Product Interactions
function initializeProductInteractions() {
    console.log('Initializing product interactions...');
    
    // Add to cart buttons
    const cartButtons = document.querySelectorAll('.add-to-cart');
    console.log('Found', cartButtons.length, 'cart buttons');
    
    cartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Cart button clicked!');
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            console.log('Product ID:', productId, 'Name:', productName, 'Price:', productPrice);
            addToCart(productId, productName, productPrice);
        });
    });
    
    // Add to wishlist buttons
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
    console.log('Found', wishlistButtons.length, 'wishlist buttons');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Wishlist button clicked!');
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            console.log('Product ID:', productId, 'Name:', productName);
            addToWishlist(productId, productName);
        });
    });
    
    // Quantity controls
    document.querySelectorAll('.quantity-control').forEach(control => {
        const minusBtn = control.querySelector('.quantity-minus');
        const plusBtn = control.querySelector('.quantity-plus');
        const input = control.querySelector('.quantity-input');
        
        if (minusBtn) {
            minusBtn.addEventListener('click', function() {
                updateQuantity(input, -1);
            });
        }
        
        if (plusBtn) {
            plusBtn.addEventListener('click', function() {
                updateQuantity(input, 1);
            });
        }
        
        if (input) {
            input.addEventListener('change', function() {
                updateCartItemQuantity(this.dataset.productId, parseInt(this.value));
            });
        }
    });
}

// Cart Functions
function addToCart(productId, productName, productPrice, quantity = 1) {
    console.log('Adding to cart:', productId, productName, productPrice, quantity);
    
    // Send AJAX request to server to add item to cart
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    console.log('Sending request to add_to_cart.php...');
    
    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Show success message
            showNotification(data.message, 'success');
            // Update cart count
            updateCartCount();
        } else {
            // Show error message
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add item to cart', 'error');
    });
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCartToStorage();
    updateCartCount();
    displayCart();
    showNotification('Product removed from cart!', 'success');
}

function updateCartItemQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            saveCartToStorage();
            displayCart();
        }
    }
}

function getCartTotal() {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

function getCartCount() {
    return cart.reduce((count, item) => count + item.quantity, 0);
}

function displayCart() {
    const cartContainer = document.querySelector('.cart-items');
    if (!cartContainer) return;
    
    if (cart.length === 0) {
        cartContainer.innerHTML = '<p class="text-center">Your cart is empty</p>';
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        html += `
            <div class="cart-item" data-product-id="${item.id}">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p class="text-primary">$${item.price.toFixed(2)}</p>
                </div>
                <div class="cart-item-quantity">
                    <div class="quantity-control">
                        <button class="quantity-minus btn btn-sm">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" data-product-id="${item.id}">
                        <button class="quantity-plus btn btn-sm">+</button>
                    </div>
                </div>
                <div class="cart-item-total">
                    <p class="text-primary">$${(item.price * item.quantity).toFixed(2)}</p>
                </div>
                <div class="cart-item-actions">
                    <button class="btn btn-danger btn-sm remove-from-cart" data-product-id="${item.id}">Remove</button>
                </div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = html;
    
    // Update total
    const totalElement = document.querySelector('.cart-total');
    if (totalElement) {
        totalElement.textContent = `$${getCartTotal().toFixed(2)}`;
    }
    
    // Re-initialize quantity controls
    initializeProductInteractions();
    
    // Add remove button listeners
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            removeFromCart(this.dataset.productId);
        });
    });
}

// Wishlist Functions
function addToWishlist(productId, productName) {
    console.log('Adding to wishlist:', productId, productName);
    
    // Send AJAX request to server to add item to wishlist
    const formData = new FormData();
    formData.append('product_id', productId);
    
    console.log('Sending request to add_to_wishlist.php...');
    
    fetch('add_to_wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Show success message
            showNotification(data.message, 'success');
            // Update wishlist count
            updateWishlistCount();
        } else {
            // Show error message
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add item to wishlist', 'error');
    });
}

function removeFromWishlist(productId) {
    wishlist = wishlist.filter(item => item.id !== productId);
    saveWishlistToStorage();
    updateWishlistCount();
    displayWishlist();
    showNotification('Product removed from wishlist!', 'success');
}

function displayWishlist() {
    const wishlistContainer = document.querySelector('.wishlist-items');
    if (!wishlistContainer) return;
    
    if (wishlist.length === 0) {
        wishlistContainer.innerHTML = '<p class="text-center">Your wishlist is empty</p>';
        return;
    }
    
    // This would typically fetch product details from the server
    // For now, we'll show a simple list
    let html = '';
    wishlist.forEach(item => {
        html += `
            <div class="wishlist-item" data-product-id="${item.id}">
                <div class="wishlist-item-info">
                    <h4>${item.name}</h4>
                </div>
                <div class="wishlist-item-actions">
                    <button class="btn btn-primary btn-sm add-to-cart-from-wishlist" data-product-id="${item.id}">Add to Cart</button>
                    <button class="btn btn-danger btn-sm remove-from-wishlist" data-product-id="${item.id}">Remove</button>
                </div>
            </div>
        `;
    });
    
    wishlistContainer.innerHTML = html;
    
    // Add event listeners
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            removeFromWishlist(this.dataset.productId);
        });
    });
}

// Storage Functions
function saveCartToStorage() {
    localStorage.setItem('glamcart_cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const stored = localStorage.getItem('glamcart_cart');
    if (stored) {
        cart = JSON.parse(stored);
    }
}

function saveWishlistToStorage() {
    localStorage.setItem('glamcart_wishlist', JSON.stringify(wishlist));
}

function loadWishlistFromStorage() {
    const stored = localStorage.getItem('glamcart_wishlist');
    if (stored) {
        wishlist = JSON.parse(stored);
    }
}

// Update Count Functions
function updateCartCount() {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        // Fetch cart count from server
        fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            cartCount.textContent = count;
            cartCount.style.display = count > 0 ? 'flex' : 'none';
        })
        .catch(error => {
            console.error('Error fetching cart count:', error);
            cartCount.textContent = '0';
            cartCount.style.display = 'none';
        });
    }
}

function updateWishlistCount() {
    const wishlistCount = document.querySelector('.wishlist-count');
    if (wishlistCount) {
        // Fetch wishlist count from server
        fetch('get_wishlist_count.php')
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            wishlistCount.textContent = count;
            wishlistCount.style.display = count > 0 ? 'flex' : 'none';
        })
        .catch(error => {
            console.error('Error fetching wishlist count:', error);
            wishlistCount.textContent = '0';
            wishlistCount.style.display = 'none';
        });
    }
}

// Utility Functions
function updateQuantity(input, change) {
    const newValue = parseInt(input.value) + change;
    if (newValue >= 1) {
        input.value = newValue;
        if (input.dataset.productId) {
            updateCartItemQuantity(input.dataset.productId, newValue);
        }
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification`;
    notification.textContent = message;
    
    // Add styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'slideIn 0.3s ease';
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Form Validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });
    
    // Email validation
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            showFieldError(input, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(input, message) {
    clearFieldError(input);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--danger)';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    input.parentNode.appendChild(errorDiv);
    input.style.borderColor = 'var(--danger)';
}

function clearFieldError(input) {
    const errorDiv = input.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    input.style.borderColor = '';
}

// AJAX Helper Functions
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

// Checkout Functions
function initializeCheckout() {
    const checkoutForm = document.querySelector('.checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm(this)) {
                processCheckout();
            }
        });
    }
}

function processCheckout() {
    const form = document.querySelector('.checkout-form');
    const formData = new FormData(form);
    
    // Add cart data
    formData.append('cart', JSON.stringify(cart));
    formData.append('total', getCartTotal().toFixed(2));
    
    // Submit form
    fetch('process_checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            cart = [];
            saveCartToStorage();
            updateCartCount();
            
            // Redirect to success page
            window.location.href = `order_success.php?order_id=${data.order_id}`;
        } else {
            showNotification(data.message || 'Checkout failed', 'danger');
        }
    })
    .catch(error => {
        console.error('Checkout error:', error);
        showNotification('An error occurred during checkout', 'danger');
    });
}

// Admin Functions
function initializeAdmin() {
    // Initialize admin-specific functionality
    initializeDataTables();
    initializeCharts();
}

function initializeDataTables() {
    // Initialize sortable tables if needed
    const tables = document.querySelectorAll('.sortable-table');
    tables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.addEventListener('click', function() {
                sortTable(table, this.dataset.sort);
            });
        });
    });
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isAscending = table.dataset.sortDirection !== 'asc';
    
    rows.sort((a, b) => {
        const aValue = a.querySelector(`td[data-${column}]`).dataset[column];
        const bValue = b.querySelector(`td[data-${column}]`).dataset[column];
        
        if (isAscending) {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
    table.dataset.sortDirection = isAscending ? 'asc' : 'desc';
}

function initializeCharts() {
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        // Chart initialization code would go here
        console.log('Charts initialized');
    }
}

// Export functions for global use
window.GlamCart = {
    addToCart,
    removeFromCart,
    addToWishlist,
    removeFromWishlist,
    getCartTotal,
    getCartCount,
    displayCart,
    displayWishlist,
    showNotification,
    validateForm
};
