@extends('layouts.app') {{-- Use your main layout --}}

@section('content')
<div class="container">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1>Product Inventory</h1>
        </div>
        <div class="col text-end">
            <button class="btn btn-success" id="addProductBtn" data-bs-toggle="modal" data-bs-target="#productModal">Add Product</button>
        </div>
    </div>

    {{-- Alert for messages --}}
    <div id="product-message" class="alert" style="display: none;"></div>

    {{-- Product table will be populated by JavaScript --}}
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="productsTableBody">
            {{-- Initial loading message --}}
            <tr>
                <td colspan="6" class="text-center">Loading products...</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Product Modal (for Add/Edit) -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId" name="productId">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="productQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="productQuantity" name="quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="productStatus" class="form-label">Status</label>
                        <select class="form-select" id="productStatus" name="status">
                            <option value="in stock" selected>In Stock</option>
                            <option value="out of stock">Out of Stock</option>
                        </select>
                    </div>
                    <div id="product-form-error-message" class="alert alert-danger" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsTableBody = document.getElementById('productsTableBody');
    const productMessageDiv = document.getElementById('product-message');

    // Modal elements
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productModalLabel = document.getElementById('productModalLabel');
    const productForm = document.getElementById('productForm');
    const productIdInput = document.getElementById('productId');
    const productNameInput = document.getElementById('productName');
    const productPriceInput = document.getElementById('productPrice');
    const productQuantityInput = document.getElementById('productQuantity');
    const productStatusInput = document.getElementById('productStatus');
    const saveProductBtn = document.getElementById('saveProductBtn');
    const addProductBtn = document.getElementById('addProductBtn'); // The button that opens the modal
    const productFormErrorDiv = document.getElementById('product-form-error-message');

    let currentEditProductId = null; // To track if we are editing or adding

    // --- Utility Functions ---
    function getToken() {
        return localStorage.getItem('access_token');
    }

    function showProductMessage(message, type = 'success') {
        productMessageDiv.textContent = message;
        productMessageDiv.className = `alert alert-${type}`; // e.g., alert-success, alert-danger
        productMessageDiv.style.display = 'block';
        setTimeout(() => {
            productMessageDiv.style.display = 'none';
        }, 3000); // Hide after 3 seconds
    }

    function showProductFormError(message) {
        productFormErrorDiv.textContent = message;
        productFormErrorDiv.style.display = 'block';
    }
    function hideProductFormError() {
        productFormErrorDiv.style.display = 'none';
        productFormErrorDiv.textContent = '';
    }


    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>"']/g, function (match) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
        });
    }

    // --- API Call Function ---
    async function apiCall(endpoint, method = 'GET', body = null) {
        const token = getToken();
        if (!token && endpoint !== '/api/login' && endpoint !== '/api/register') { // Allow unauth for login/register
            window.location.href = '/login'; // Redirect if no token for protected routes
            throw new Error('No token found, redirecting to login.');
        }

        const headers = {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        };
        if (body) {
            headers['Content-Type'] = 'application/json';
        }

        const config = { method, headers };
        if (body) {
            config.body = JSON.stringify(body);
        }

        const response = await fetch(endpoint, config);

        if (response.status === 401) { // Unauthorized or token expired
            localStorage.removeItem('access_token');
            localStorage.removeItem('user');
            showProductMessage('Session expired. Please log in again.', 'danger');
            setTimeout(() => window.location.href = '/login', 1500);
            throw new Error('Unauthorized');
        }
        return response;
    }


    // --- Product Rendering ---
    function renderProducts(products) {
        productsTableBody.innerHTML = ''; // Clear loading/previous data

        if (!products || products.length === 0) {
            productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center">No products found.</td></tr>`;
            return;
        }

        products.forEach(product => {
            const statusBadgeClass = product.status === 'in stock' ? 'bg-success' : 'bg-danger';
            const row = `
                <tr id="product-row-${product.id}">
                    <td>${product.id}</td>
                    <td>${escapeHTML(product.name)}</td>
                    <td>${parseFloat(product.price).toFixed(2)}</td>
                    <td>${product.quantity}</td>
                    <td><span class="badge ${statusBadgeClass}">${escapeHTML(product.status)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" data-id="${product.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${product.id}">Delete</button>
                    </td>
                </tr>
            `;
            productsTableBody.insertAdjacentHTML('beforeend', row);
        });

        // Re-attach event listeners for new buttons
        attachActionListeners();
    }

    function attachActionListeners() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() { handleOpenEditModal(this.dataset.id); });
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() { handleDeleteProduct(this.dataset.id); });
        });
    }

    // --- Fetch Products ---
    async function fetchProducts() {
        productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center">Loading products...</td></tr>`;
        try {
            const response = await apiCall('/api/products');
            if (response.ok) {
                const products = await response.json();
                renderProducts(products);
            } else {
                const errorData = await response.json().catch(() => ({ message: 'Failed to load products.' }));
                console.error('Failed to fetch products:', response.statusText, errorData);
                showProductMessage(`Error loading products: ${errorData.message || response.statusText}`, 'danger');
                productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Failed to load products.</td></tr>`;
            }
        } catch (error) {
            if (error.message !== 'Unauthorized' && error.message !== 'No token found, redirecting to login.') { // Avoid double message if apiCall handles it
                console.error('Error fetching products:', error);
                showProductMessage('An error occurred while loading products.', 'danger');
                productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">An error occurred.</td></tr>`;
            }
        }
    }

    // --- Add Product ---
    addProductBtn.addEventListener('click', function() {
        currentEditProductId = null; // Ensure we are in "add" mode
        productModalLabel.textContent = 'Add Product';
        productForm.reset(); // Clear form fields
        productIdInput.value = ''; // Clear hidden ID
        hideProductFormError();
        // Modal is opened via data-bs-toggle attributes, no need for productModal.show() here
    });

    // --- Edit Product ---
    async function handleOpenEditModal(productId) {
        currentEditProductId = productId;
        productModalLabel.textContent = 'Edit Product';
        productForm.reset();
        hideProductFormError();

        try {
            const response = await apiCall(`/api/products/${productId}`);
            if (response.ok) {
                const product = await response.json();
                productIdInput.value = product.id;
                productNameInput.value = product.name;
                productPriceInput.value = parseFloat(product.price).toFixed(2);
                productQuantityInput.value = product.quantity;
                productStatusInput.value = product.status;
                productModal.show();
            } else {
                showProductMessage('Failed to load product details for editing.', 'danger');
            }
        } catch (error) {
             if (error.message !== 'Unauthorized') {
                showProductMessage('Error fetching product details.', 'danger');
             }
        }
    }

    // --- Save Product (Add or Update) ---
    saveProductBtn.addEventListener('click', async function() {
        hideProductFormError();
        const productData = {
            name: productNameInput.value.trim(),
            price: parseFloat(productPriceInput.value),
            quantity: parseInt(productQuantityInput.value),
            status: productStatusInput.value
        };

        // Basic frontend validation
        if (!productData.name || isNaN(productData.price) || productData.price < 0 || isNaN(productData.quantity) || productData.quantity < 0) {
            showProductFormError('Please fill in all fields correctly. Price and quantity cannot be negative.');
            return;
        }

        let url = '/api/products';
        let method = 'POST';

        if (currentEditProductId) { // If editing
            url = `/api/products/${currentEditProductId}`;
            method = 'PUT';
        }

        try {
            const response = await apiCall(url, method, productData);
            const responseData = await response.json();

            if (response.ok) {
                showProductMessage(`Product ${currentEditProductId ? 'updated' : 'added'} successfully!`, 'success');
                productModal.hide();
                fetchProducts(); // Refresh the product list
            } else {
                // Handle validation errors from API
                if (responseData.errors) {
                    const errors = Object.values(responseData.errors).flat().join(' ');
                    showProductFormError(`Validation failed: ${errors}`);
                } else {
                    showProductFormError(responseData.message || `Failed to ${currentEditProductId ? 'update' : 'add'} product.`);
                }
            }
        } catch (error) {
            if (error.message !== 'Unauthorized') {
                showProductFormError(`An error occurred: ${error.message}`);
            }
        }
    });

    // --- Delete Product ---
    async function handleDeleteProduct(productId) {
        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }
        try {
            const response = await apiCall(`/api/products/${productId}`, 'DELETE');
            if (response.ok) {
                showProductMessage('Product deleted successfully!', 'success');
                // Optionally remove the row directly from DOM or just refetch
                // document.getElementById(`product-row-${productId}`).remove();
                fetchProducts(); // Easiest way to update list
            } else {
                const errorData = await response.json().catch(() => ({ message: 'Failed to delete product.' }));
                showProductMessage(`Error deleting product: ${errorData.message || response.statusText}`, 'danger');
            }
        } catch (error) {
            if (error.message !== 'Unauthorized') {
                showProductMessage('An error occurred while deleting the product.', 'danger');
            }
        }
    }

    // --- Initial Setup ---
    if (!getToken()) { // If no token on page load, redirect to login
        window.location.href = '/login';
    } else {
        fetchProducts(); // Fetch products if token exists
    }

    // Handle logout (example, you'll need a logout button in your layout)
    // This is just a placeholder for where you might put logout logic
    const logoutButton = document.getElementById('logout-button'); // Assuming you add a button with this ID
    if (logoutButton) {
        logoutButton.addEventListener('click', async function() {
            try {
                await apiCall('/api/logout', 'POST');
            } catch (error) {
                // Even if API logout fails, clear client-side token
                console.error("Logout API call failed, but clearing client session.", error);
            } finally {
                localStorage.removeItem('access_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        });
    }
});
</script>
@endpush
