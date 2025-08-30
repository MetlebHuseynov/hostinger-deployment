class ProductsManager {
    constructor() {
        console.log('ProductsManager constructor called');
        this.products = [];
        this.categories = [];
        this.markas = [];
        this.editingProductId = null;
        this.apiUrl = `${window.location.origin}/api`;
        
        console.log('Calling init...');
        this.init();
        console.log('Init completed');
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
        // Add product button
        const addBtn = document.getElementById('add-product-btn');
        console.log('Add button found:', addBtn);
        if (addBtn) {
            addBtn.addEventListener('click', () => {
                console.log('Add button clicked!');
                this.openModal();
            });
        } else {
            console.error('Add button not found!');
        }

        // Modal Close Buttons
        const cancelBtn = document.getElementById('cancel-btn');
        const closeBtn = document.getElementById('close-modal-btn');
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeModal());
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeModal());
        }

        // Form Submit
        const form = document.getElementById('product-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }

        // Filter Events
        const categoryFilter = document.getElementById('category-filter');
        const brandFilter = document.getElementById('brand-filter');
        const statusFilter = document.getElementById('status-filter');
        const clearFilters = document.getElementById('clear-filters');

        if (categoryFilter) categoryFilter.addEventListener('change', () => this.filterProducts());
        if (brandFilter) brandFilter.addEventListener('change', () => this.filterProducts());
        if (statusFilter) statusFilter.addEventListener('change', () => this.filterProducts());
        if (clearFilters) clearFilters.addEventListener('click', () => this.clearFilters());

        // Image upload events
        const imageUrlRadio = document.getElementById('image-url-radio');
        const imageFileRadio = document.getElementById('image-file-radio');
        const imageFileInput = document.getElementById('product-image-file');
        const imageUrlInput = document.getElementById('product-image-url');
        const removeImageBtn = document.getElementById('remove-image-btn');

        if (imageUrlRadio) {
            imageUrlRadio.addEventListener('change', () => this.toggleImageInput());
        }
        if (imageFileRadio) {
            imageFileRadio.addEventListener('change', () => this.toggleImageInput());
        }
        if (imageFileInput) {
            imageFileInput.addEventListener('change', (e) => this.handleImageFileSelect(e));
        }
        if (imageUrlInput) {
            imageUrlInput.addEventListener('input', (e) => this.handleImageUrlInput(e));
        }
        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', () => this.removeImage());
        }
    }

    async loadData() {
        try {
            console.log('Loading data...');
            await Promise.all([
                this.loadProducts(),
                this.loadCategories(),
                this.loadMarkas()
            ]);
            this.populateFilters();
        } catch (error) {
            console.error('Error loading data:', error);
            this.showAlert('Məlumatlar yüklənərkən xəta baş verdi', 'danger');
        }
    }

    async loadProducts() {
        try {
            const response = await fetch(`${this.apiUrl}/products`);
            if (!response.ok) throw new Error('Failed to load products');
            const data = await response.json();
            this.products = data.data ? data.data.products : [];
            this.renderProducts();
        } catch (error) {
            console.error('Error loading products:', error);
            throw error;
        }
    }

    async loadCategories() {
        try {
            const response = await fetch(`${this.apiUrl}/categories`);
            if (!response.ok) throw new Error('Failed to load categories');
            const data = await response.json();
            this.categories = data.data || [];
        } catch (error) {
            console.error('Error loading categories:', error);
            throw error;
        }
    }

    async loadMarkas() {
        try {
            const response = await fetch(`${this.apiUrl}/markas`);
            if (!response.ok) throw new Error('Failed to load markas');
            const data = await response.json();
            this.markas = data.data || [];
        } catch (error) {
            console.error('Error loading markas:', error);
            throw error;
        }
    }

    populateFilters() {
        // Populate category filter
        const categoryFilter = document.getElementById('category-filter');
        const productCategory = document.getElementById('product-category');
        
        if (categoryFilter) {
            categoryFilter.innerHTML = '<option value="">Bütün kateqoriyalar</option>';
            if (this.categories && Array.isArray(this.categories)) {
                this.categories.forEach(category => {
                    categoryFilter.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                });
            }
        }

        if (productCategory) {
            productCategory.innerHTML = '<option value="">Kateqoriya seçin</option>';
            if (this.categories && Array.isArray(this.categories)) {
                this.categories.forEach(category => {
                    productCategory.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                });
            }
        }

        // Populate brand filter
        const brandFilter = document.getElementById('brand-filter');
        const productMarka = document.getElementById('product-marka');
        
        if (brandFilter) {
            brandFilter.innerHTML = '<option value="">Bütün markalar</option>';
            if (this.markas && Array.isArray(this.markas)) {
                this.markas.forEach(marka => {
                    brandFilter.innerHTML += `<option value="${marka.id}">${marka.name}</option>`;
                });
            }
        }

        if (productMarka) {
            productMarka.innerHTML = '<option value="">Marka seçin</option>';
            if (this.markas && Array.isArray(this.markas)) {
                this.markas.forEach(marka => {
                    productMarka.innerHTML += `<option value="${marka.id}">${marka.name}</option>`;
                });
            }
        }
    }

    openModal(product = null) {
        console.log('Opening modal for product:', product);
        
        const modal = document.getElementById('product-modal');
        const title = document.getElementById('modal-title');
        const form = document.getElementById('product-form');
        
        if (!modal) {
            console.error('Modal not found!');
            return;
        }

        this.currentProduct = product;
        
        if (product) {
            title.textContent = 'Məhsulu Redaktə Et';
            this.populateForm(product);
        } else {
            title.textContent = 'Yeni Məhsul Əlavə Et';
            form.reset();
        }

        // Show modal
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const firstInput = document.getElementById('product-name');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    closeModal() {
        const modal = document.getElementById('product-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
        this.currentProduct = null;
    }

    populateForm(product) {
        document.getElementById('product-name').value = product.name || '';
        document.getElementById('product-price').value = product.price || '';
        document.getElementById('product-description').value = product.description || '';
        document.getElementById('product-stock').value = product.stock || '';
        document.getElementById('product-category').value = product.category_id || '';
        document.getElementById('product-marka').value = product.marka_id || '';
        document.getElementById('product-image-url').value = product.image || '';
        document.getElementById('product-status').value = product.status || 'active';
    }

    async handleSubmit() {
        const form = document.getElementById('product-form');
        const formData = new FormData(form);
        
        // Convert FormData to JSON object
        const productData = {
            name: formData.get('name'),
            description: formData.get('description'),
            price: parseFloat(formData.get('price')),
            category_id: formData.get('category_id') ? parseInt(formData.get('category_id')) : null,
            marka_id: formData.get('marka_id') ? parseInt(formData.get('marka_id')) : null,
            stock: parseInt(formData.get('stock')) || 0
        };
        
        // Handle image
        const imageType = document.querySelector('input[name="imageType"]:checked')?.value || 'url';
        if (imageType === 'url') {
            const imageUrl = formData.get('image_url');
            if (imageUrl) {
                productData.image = imageUrl;
            }
        }
        // Note: File upload will be handled separately if needed

        try {
            const token = localStorage.getItem('token') || sessionStorage.getItem('token');
            
            let response;
            if (this.currentProduct) {
                // Update existing product
                response = await fetch(`${this.apiUrl}/products/${this.currentProduct.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(productData)
                });
            } else {
                // Create new product
                response = await fetch(`${this.apiUrl}/products`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(productData)
                });
            }

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to save product');
            }

            const result = await response.json();
            console.log('Product saved:', result);
            
            this.showAlert(
                this.currentProduct ? 'Məhsul uğurla yeniləndi' : 'Məhsul uğurla əlavə edildi',
                'success'
            );
            
            this.closeModal();
            await this.loadProducts();
            
        } catch (error) {
            console.error('Error saving product:', error);
            this.showAlert('Məhsul saxlanılarkən xəta baş verdi: ' + error.message, 'danger');
        }
    }

    renderProducts() {
        const tbody = document.querySelector('#products-table tbody');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        this.products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td>
                    ${product.image && product.image !== 'null' ? `<img src="${product.image}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">` : `<img src="/images/product-placeholder.svg" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; opacity: 0.6;">`}
                </td>
                <td>${product.name}</td>
                <td>${product.price ? parseFloat(product.price).toFixed(2) + ' ₼' : 'N/A'}</td>
                <td>${product.stock || 0}</td>
                <td>${product.category_name || 'N/A'}</td>
                <td>${product.marka_name || 'N/A'}</td>
                <td>
                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                        ${product.status === 'active' ? 'Aktiv' : 'Qeyri-aktiv'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="productsManager.editProduct(${product.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="productsManager.deleteProduct(${product.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    getCategoryName(categoryId) {
        if (!this.categories || !Array.isArray(this.categories)) return 'N/A';
        const category = this.categories.find(c => c.id == categoryId);
        return category ? category.name : 'N/A';
    }

    getMarkaName(markaId) {
        if (!this.markas || !Array.isArray(this.markas)) return 'N/A';
        const marka = this.markas.find(m => m.id == markaId);
        return marka ? marka.name : 'N/A';
    }

    editProduct(id) {
        const product = this.products.find(p => p.id == id);
        if (product) {
            this.openModal(product);
        }
    }

    async deleteProduct(id) {
        if (!confirm('Bu məhsulu silmək istədiyinizə əminsiniz?')) {
            return;
        }

        try {
            const token = localStorage.getItem('token') || sessionStorage.getItem('token');
            
            const response = await fetch(`${this.apiUrl}/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete product');
            }

            this.showAlert('Məhsul uğurla silindi', 'success');
            await this.loadProducts();
            
        } catch (error) {
            console.error('Error deleting product:', error);
            this.showAlert('Məhsul silinərkən xəta baş verdi', 'danger');
        }
    }

    filterProducts() {
        const categoryFilter = document.getElementById('category-filter').value;
        const brandFilter = document.getElementById('brand-filter').value;
        const statusFilter = document.getElementById('status-filter').value;

        let filteredProducts = this.products;

        if (categoryFilter) {
            filteredProducts = filteredProducts.filter(p => p.category_id == categoryFilter);
        }
        if (brandFilter) {
            filteredProducts = filteredProducts.filter(p => p.marka_id == brandFilter);
        }
        if (statusFilter) {
            filteredProducts = filteredProducts.filter(p => p.status === statusFilter);
        }

        this.renderFilteredProducts(filteredProducts);
    }

    renderFilteredProducts(products) {
        const tbody = document.querySelector('#products-table tbody');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td>
                    ${product.imageUrl ? `<img src="${product.imageUrl}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">` : '<span class="text-muted">Şəkil yoxdur</span>'}
                </td>
                <td>${product.name}</td>
                <td>${product.price ? parseFloat(product.price).toFixed(2) + ' ₼' : 'N/A'}</td>
                <td>${product.stock || 0}</td>
                <td>${this.getCategoryName(product.categoryId)}</td>
                <td>${this.getMarkaName(product.markaId)}</td>
                <td>
                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                        ${product.status === 'active' ? 'Aktiv' : 'Qeyri-aktiv'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="productsManager.editProduct(${product.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="productsManager.deleteProduct(${product.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    clearFilters() {
        document.getElementById('category-filter').value = '';
        document.getElementById('brand-filter').value = '';
        document.getElementById('status-filter').value = '';
        this.renderProducts();
    }

    async uploadImage(file) {
        const formData = new FormData();
        formData.append('file', file);
        
        const response = await fetch(`${this.apiUrl}/upload`, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Upload failed');
        }
        
        const result = await response.json();
        return result.data.url;
    }
    
    toggleImageInput() {
        const imageType = document.querySelector('input[name="imageType"]:checked').value;
        const urlGroup = document.getElementById('url-input-group');
        const fileGroup = document.getElementById('file-input-group');
        
        if (imageType === 'url') {
            urlGroup.style.display = 'block';
            fileGroup.style.display = 'none';
        } else {
            urlGroup.style.display = 'none';
            fileGroup.style.display = 'block';
        }
        
        this.hideImagePreview();
    }
    
    handleImageFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.showImagePreview(URL.createObjectURL(file));
        } else {
            this.hideImagePreview();
        }
    }
    
    handleImageUrlInput(event) {
        const url = event.target.value.trim();
        if (url) {
            this.showImagePreview(url);
        } else {
            this.hideImagePreview();
        }
    }
    
    showImagePreview(src) {
        const container = document.getElementById('image-preview-container');
        const preview = document.getElementById('image-preview');
        
        preview.src = src;
        container.style.display = 'block';
    }
    
    hideImagePreview() {
        const container = document.getElementById('image-preview-container');
        container.style.display = 'none';
    }
    
    removeImage() {
        document.getElementById('product-image-url').value = '';
        document.getElementById('product-image-file').value = '';
        this.hideImagePreview();
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing ProductsManager');
    window.productsManager = new ProductsManager();
});