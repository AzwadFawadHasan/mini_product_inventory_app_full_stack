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
    <div class="mb-3 text-end">
        <button id="logout-button" class="btn btn-outline-danger">Logout</button>
    </div>

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
<!-- Ensure Bootstrap JS is loaded before this script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('[PRODUCTS SCRIPT] DOMContentLoaded'); // DEBUG

    const productsTableBody = document.getElementById('productsTableBody');
    const productMessageDiv = document.getElementById('product-message');

    // Modal elements
    let productModalInstance; // Declare here
    const productModalElement = document.getElementById('productModal');
    if (productModalElement && typeof bootstrap !== 'undefined') { // DEBUG: Check bootstrap
        productModalInstance = new bootstrap.Modal(productModalElement);
        console.log('[PRODUCTS SCRIPT] Bootstrap Modal instance created.'); // DEBUG
    } else {
        console.error('[PRODUCTS SCRIPT] Bootstrap Modal element not found or bootstrap JS not loaded.'); // DEBUG
    }

    const productModalLabel = document.getElementById('productModalLabel');
    const productForm = document.getElementById('productForm');
    const productIdInput = document.getElementById('productId');
    const productNameInput = document.getElementById('productName');
    const productPriceInput = document.getElementById('productPrice');
    const productQuantityInput = document.getElementById('productQuantity');
    const productStatusInput = document.getElementById('productStatus');
    const saveProductBtn = document.getElementById('saveProductBtn');
    const addProductBtn = document.getElementById('addProductBtn');
    const productFormErrorDiv = document.getElementById('product-form-error-message');

    let currentEditProductId = null;

    function getToken() {
        const token = localStorage.getItem('access_token');
        // console.log('[PRODUCTS SCRIPT] getToken called, token:', token ? 'found' : 'not found'); // DEBUG: Can be noisy
        return token;
    }

    function showProductMessage(message, type = 'success') {
        console.log(`[PRODUCTS SCRIPT] showProductMessage: ${message}, type: ${type}`); // DEBUG
        productMessageDiv.textContent = message;
        productMessageDiv.className = `alert alert-${type} alert-dismissible fade show`;
        productMessageDiv.style.display = 'block';
        // Add a close button for persistent messages if needed, or rely on timeout
        setTimeout(() => {
            if (productMessageDiv.style.display === 'block') { // Check if still visible
                 // productMessageDiv.style.display = 'none'; // Or use Bootstrap's close method if available
                 const bsAlert = bootstrap.Alert.getInstance(productMessageDiv);
                 if (bsAlert) bsAlert.close(); else productMessageDiv.style.display = 'none';
            }
        }, 5000);
    }

    function showProductFormError(message) {
        console.warn('[PRODUCTS SCRIPT] showProductFormError:', message); // DEBUG
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

    async function apiCall(endpoint, method = 'GET', body = null) {
        console.log(`[PRODUCTS SCRIPT] apiCall: ${method} ${endpoint}`, body || ''); // DEBUG
        const token = getToken();
        if (!token && endpoint !== '/api/login' && endpoint !== '/api/register') {
            console.warn('[PRODUCTS SCRIPT] apiCall: No token, redirecting to login.'); // DEBUG
            window.location.href = '/login';
            throw new Error('No token found, redirecting to login.');
        }

        const headers = { 'Accept': 'application/json' };
        if (token) { // Only add Auth header if token exists
            headers['Authorization'] = `Bearer ${token}`;
        }
        if (body) {
            headers['Content-Type'] = 'application/json';
        }

        const config = { method, headers };
        if (body) {
            config.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(endpoint, config);
            console.log(`[PRODUCTS SCRIPT] apiCall response status for ${method} ${endpoint}: ${response.status}`); // DEBUG

            if (response.status === 401) {
                console.warn('[PRODUCTS SCRIPT] apiCall: 401 Unauthorized. Clearing token and redirecting.'); // DEBUG
                localStorage.removeItem('access_token');
                localStorage.removeItem('user');
                showProductMessage('Session expired. Please log in again.', 'danger');
                setTimeout(() => window.location.href = '/login', 1500);
                throw new Error('Unauthorized');
            }
            return response;
        } catch (error) {
            console.error(`[PRODUCTS SCRIPT] apiCall fetch error for ${method} ${endpoint}:`, error); // DEBUG
            throw error; // Re-throw to be caught by caller
        }
    }

    function renderProducts(products) {
        console.log('[PRODUCTS SCRIPT] renderProducts called with:', products); // DEBUG
        productsTableBody.innerHTML = '';

        if (!products || products.length === 0) {
            console.log('[PRODUCTS SCRIPT] No products to render.'); // DEBUG
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
        console.log('[PRODUCTS SCRIPT] Products rendered. Attaching action listeners.'); // DEBUG
        attachActionListeners();
    }

    function attachActionListeners() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.removeEventListener('click', handleEditButtonClick); // Remove old if any
            button.addEventListener('click', handleEditButtonClick);
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteButtonClick); // Remove old if any
            button.addEventListener('click', handleDeleteButtonClick);
        });
        console.log('[PRODUCTS SCRIPT] Edit and Delete listeners attached.'); // DEBUG
    }
    // Define handlers separately to make removeEventListener work reliably
    function handleEditButtonClick() { handleOpenEditModal(this.dataset.id); }
    function handleDeleteButtonClick() { handleDeleteProduct(this.dataset.id); }


    async function fetchProducts() {
        console.log('[PRODUCTS SCRIPT] fetchProducts called'); // DEBUG
        productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center">Loading products...</td></tr>`;
        try {
            const response = await apiCall('/api/products');
            if (response.ok) {
                const products = await response.json();
                console.log('[PRODUCTS SCRIPT] Products fetched successfully from API:', products); // DEBUG
                renderProducts(products);
            } else {
                const errorText = await response.text(); // Get text for better debugging
                console.error('[PRODUCTS SCRIPT] Failed to fetch products API response not OK. Status:', response.status, 'Response Text:', errorText); // DEBUG
                let errorMsg = 'Failed to load products.';
                try {
                    const errorData = JSON.parse(errorText);
                    errorMsg = errorData.message || errorMsg;
                } catch (e) { /* Ignore if not JSON */ }
                showProductMessage(`Error loading products: ${errorMsg}`, 'danger');
                productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Failed to load products. See console.</td></tr>`;
            }
        } catch (error) {
            console.error('[PRODUCTS SCRIPT] Catch block in fetchProducts:', error); // DEBUG
            if (error.message !== 'Unauthorized' && error.message !== 'No token found, redirecting to login.') {
                showProductMessage('An error occurred while loading products.', 'danger');
                productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">An error occurred. See console.</td></tr>`;
            }
        }
    }

    if (addProductBtn) { // DEBUG: Check if button exists
        addProductBtn.addEventListener('click', function() {
            console.log('[PRODUCTS SCRIPT] Add Product button clicked'); // DEBUG
            currentEditProductId = null;
            if (productModalLabel) productModalLabel.textContent = 'Add Product';
            if (productForm) productForm.reset();
            if (productIdInput) productIdInput.value = '';
            hideProductFormError();
            // Modal is opened via data-bs-toggle attributes on the button
        });
    } else {
        console.error('[PRODUCTS SCRIPT] Add Product button (addProductBtn) not found!'); // DEBUG
    }


    async function handleOpenEditModal(productId) {
        console.log('[PRODUCTS SCRIPT] handleOpenEditModal for ID:', productId); // DEBUG
        currentEditProductId = productId;
        if (productModalLabel) productModalLabel.textContent = 'Edit Product';
        if (productForm) productForm.reset();
        hideProductFormError();

        try {
            const response = await apiCall(`/api/products/${productId}`);
            if (response.ok) {
                const product = await response.json();
                console.log('[PRODUCTS SCRIPT] Product details for edit fetched:', product); // DEBUG
                if(productIdInput) productIdInput.value = product.id;
                if(productNameInput) productNameInput.value = product.name;
                if(productPriceInput) productPriceInput.value = parseFloat(product.price).toFixed(2);
                if(productQuantityInput) productQuantityInput.value = product.quantity;
                if(productStatusInput) productStatusInput.value = product.status;
                // Ensure modal instance exists before showing
                if (!productModalInstance && typeof bootstrap !== 'undefined' && productModalElement) {
                    productModalInstance = new bootstrap.Modal(productModalElement);
                }
                if(productModalInstance) {
                    productModalInstance.show();
                } else {
                    console.error("Modal instance not available to show for edit");
                }
            } else {
                console.error('[PRODUCTS SCRIPT] Failed to load product details for editing. Status:', response.status); // DEBUG
                showProductMessage('Failed to load product details for editing.', 'danger');
            }
        } catch (error) {
             console.error('[PRODUCTS SCRIPT] Error in handleOpenEditModal:', error); // DEBUG
             if (error.message !== 'Unauthorized') {
                showProductMessage('Error fetching product details.', 'danger');
             }
        }
    }

    if (saveProductBtn) { // DEBUG: Check if button exists
        saveProductBtn.addEventListener('click', async function() {
            console.log('[PRODUCTS SCRIPT] Save Product button clicked. currentEditProductId:', currentEditProductId); // DEBUG
            hideProductFormError();
            const productData = {
                name: productNameInput.value.trim(),
                price: parseFloat(productPriceInput.value),
                quantity: parseInt(productQuantityInput.value),
                status: productStatusInput.value
            };
            console.log('[PRODUCTS SCRIPT] Product data from form:', productData); // DEBUG

            if (!productData.name || isNaN(productData.price) || productData.price < 0 || isNaN(productData.quantity) || productData.quantity < 0) {
                showProductFormError('Please fill in all fields correctly. Price and quantity cannot be negative.');
                console.warn('[PRODUCTS SCRIPT] Frontend validation failed for save product.'); // DEBUG
                return;
            }

            let url = '/api/products';
            let method = 'POST';

            if (currentEditProductId) {
                url = `/api/products/${currentEditProductId}`;
                method = 'PUT';
            }
            console.log(`[PRODUCTS SCRIPT] Saving product. Method: ${method}, URL: ${url}`); // DEBUG

            try {
                const response = await apiCall(url, method, productData);
                let responseData = {};
                try {
                    responseData = await response.json();
                    console.log('[PRODUCTS SCRIPT] Save product API response data:', responseData); // DEBUG
                } catch (e) {
                    console.error('[PRODUCTS SCRIPT] Failed to parse JSON from save product response. Status:', response.status, 'Response Text:', await response.text()); // DEBUG
                    showProductFormError('Received an invalid response from the server.');
                    return;
                }


                if (response.ok) {
                    showProductMessage(`Product ${currentEditProductId ? 'updated' : 'added'} successfully!`, 'success');
                    // alert(productModalInstance);
                    if(productModalInstance) {
                        productModalInstance.hide();
                        // Remove lingering modal-backdrop if present
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style = '';
                    } else {
                        console.error("Modal instance not available to hide after save");
                    }
                    fetchProducts();
                } else {
                    console.warn('[PRODUCTS SCRIPT] Save product API call not OK. Status:', response.status); // DEBUG
                    if (responseData.errors) {
                        const errors = Object.values(responseData.errors).flat().join(' ');
                        showProductFormError(`Validation failed: ${errors}`);
                    } else {
                        showProductFormError(responseData.message || `Failed to ${currentEditProductId ? 'update' : 'add'} product.`);
                    }
                }
            } catch (error) {
                console.error('[PRODUCTS SCRIPT] Catch block in saveProductBtn handler:', error); // DEBUG
                if (error.message !== 'Unauthorized') {
                    showProductFormError(`An error occurred: ${error.message}`);
                }
            }
        });
    } else {
        console.error('[PRODUCTS SCRIPT] Save Product button (saveProductBtn) not found!'); // DEBUG
    }


    async function handleDeleteProduct(productId) {
        console.log('[PRODUCTS SCRIPT] handleDeleteProduct for ID:', productId); // DEBUG
        if (!confirm('Are you sure you want to delete this product?')) {
            console.log('[PRODUCTS SCRIPT] Delete cancelled by user.'); // DEBUG
            return;
        }
        try {
            const response = await apiCall(`/api/products/${productId}`, 'DELETE');
            if (response.ok) {
                showProductMessage('Product deleted successfully!', 'success');
                fetchProducts();
            } else {
                const errorText = await response.text();
                console.error('[PRODUCTS SCRIPT] Failed to delete product. Status:', response.status, 'Response Text:', errorText); // DEBUG
                let errorMsg = 'Failed to delete product.';
                try {
                    const errorData = JSON.parse(errorText);
                    errorMsg = errorData.message || errorMsg;
                } catch (e) { /* Ignore */ }
                showProductMessage(`Error deleting product: ${errorMsg}`, 'danger');
            }
        } catch (error) {
            console.error('[PRODUCTS SCRIPT] Catch block in handleDeleteProduct:', error); // DEBUG
            if (error.message !== 'Unauthorized') {
                showProductMessage('An error occurred while deleting the product.', 'danger');
            }
        }
    }

    // --- Initial Setup ---
    console.log('[PRODUCTS SCRIPT] Running initial setup to check token and fetch products.'); // DEBUG
    if (!getToken()) {
        console.warn('[PRODUCTS SCRIPT] Initial setup: No token found, redirecting to login.'); // DEBUG
        window.location.href = '/login';
    } else {
        console.log('[PRODUCTS SCRIPT] Initial setup: Token found, calling fetchProducts.'); // DEBUG
        fetchProducts();
    }

    const logoutButton = document.getElementById('logout-button');
    if (logoutButton) {
        console.log('[PRODUCTS SCRIPT] Logout button found, attaching listener.'); // DEBUG
        logoutButton.addEventListener('click', async function(event) {
            event.preventDefault(); // Prevent default if it's a link
            console.log('[PRODUCTS SCRIPT] Logout button clicked.'); // DEBUG
            try {
                await apiCall('/api/logout', 'POST');
                console.log('[PRODUCTS SCRIPT] Logout API call successful or handled.'); // DEBUG
            } catch (error) {
                console.error("[PRODUCTS SCRIPT] Logout API call failed, but clearing client session anyway.", error);
            } finally {
                console.log('[PRODUCTS SCRIPT] Clearing localStorage and redirecting to login.'); // DEBUG
                localStorage.removeItem('access_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        });
    } else {
        console.warn('[PRODUCTS SCRIPT] Logout button (logout-button) not found.'); // DEBUG
    }
});
</script>
@endpush

