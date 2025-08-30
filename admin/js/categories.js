class CategoriesManager {
    constructor() {
        this.categories = [];
        this.init();
    }

    init() {
        console.log('CategoriesManager initialized');
        this.bindEvents();
        this.loadCategories();
    }

    bindEvents() {
        // Add category button
        const addBtn = document.getElementById('add-category-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.openModal());
        }

        // Modal close events
        const closeBtn = document.querySelector('#category-modal .btn-close');
        const cancelBtn = document.getElementById('cancel-btn');
        const modal = document.getElementById('category-modal');
        
        if (closeBtn) closeBtn.addEventListener('click', () => this.closeModal());
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.closeModal());
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModal();
            });
        }

        // Form submit
        const form = document.getElementById('category-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    async loadCategories() {
        try {
            const response = await fetch('/api/categories');
            if (response.ok) {
                this.categories = await response.json();
                this.renderCategories();
            } else {
                this.showAlert('Kateqoriyalar yüklənə bilmədi', 'error');
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            this.showAlert('Kateqoriyalar yüklənərkən xəta baş verdi', 'error');
        }
    }

    renderCategories() {
        const tbody = document.getElementById('categories-table');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        if (this.categories.length === 0) {
            document.getElementById('no-data').classList.remove('d-none');
            return;
        }
        
        document.getElementById('no-data').classList.add('d-none');
        
        this.categories.forEach(category => {
            const row = document.createElement('tr');
            const imageHtml = category.image ? 
                `<img src="${category.image}" alt="${category.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">` : 
                '<i class="fas fa-image text-muted"></i>';
            
            const statusBadge = category.status === 'active' ? 
                '<span class="badge bg-success">Aktiv</span>' : 
                '<span class="badge bg-secondary">Qeyri-aktiv</span>';
            
            const createdDate = category.created_at ? 
                new Date(category.created_at).toLocaleDateString('az-AZ') : 
                'Məlum deyil';
            
            row.innerHTML = `
                <td>${category.id}</td>
                <td>${imageHtml}</td>
                <td>${category.name}</td>
                <td>${category.description || 'Təsvir yoxdur'}</td>
                <td>${category.product_count || 0}</td>
                <td>${statusBadge}</td>
                <td>${createdDate}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="categoriesManager.editCategory(${category.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="categoriesManager.deleteCategory(${category.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    openModal(categoryId = null) {
        const modal = document.getElementById('category-modal');
        const form = document.getElementById('category-form');
        const title = document.getElementById('modal-title');
        
        if (!modal || !form) {
            console.error('Modal elements not found');
            return;
        }

        // Reset form
        form.reset();
        
        if (categoryId) {
            // Edit mode
            const category = this.categories.find(c => c.id === categoryId);
            if (category) {
                document.getElementById('category-id').value = category.id;
                document.getElementById('category-name').value = category.name;
                document.getElementById('category-description').value = category.description || '';
                document.getElementById('category-status').value = category.status || 'active';
                if (title) title.textContent = 'Kateqoriyanı Redaktə Et';
            }
        } else {
            // Add mode
            document.getElementById('category-id').value = '';
            if (title) title.textContent = 'Yeni Kateqoriya Əlavə Et';
        }

        modal.style.display = 'block';
        modal.classList.add('show');
        
        // Focus first input
        const firstInput = form.querySelector('input[type="text"]');
        if (firstInput) firstInput.focus();
    }

    closeModal() {
        const modal = document.getElementById('category-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const categoryData = {
            name: formData.get('name'),
            description: formData.get('description'),
            status: formData.get('status') || 'active'
        };
        
        // Check if we have a hidden category ID field for editing
        const categoryIdField = document.getElementById('category-id');
        const categoryId = categoryIdField ? categoryIdField.value : null;
        const isEdit = categoryId && categoryId !== '';
        
        try {
            const url = isEdit ? `/api/categories/${categoryId}` : '/api/categories';
            const method = isEdit ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(categoryData)
            });
            
            if (response.ok) {
                this.showAlert(isEdit ? 'Kateqoriya uğurla yeniləndi' : 'Kateqoriya uğurla əlavə edildi', 'success');
                this.closeModal();
                this.loadCategories();
            } else {
                const error = await response.json();
                this.showAlert(error.message || 'Xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Error saving category:', error);
            this.showAlert('Kateqoriya saxlanılarkən xəta baş verdi', 'error');
        }
    }

    editCategory(categoryId) {
        this.openModal(categoryId);
    }

    async deleteCategory(categoryId) {
        if (!confirm('Bu kateqoriyanı silmək istədiyinizə əminsiniz?')) {
            return;
        }
        
        try {
            const response = await fetch(`/api/categories/${categoryId}`, {
                method: 'DELETE'
            });
            
            if (response.ok) {
                this.showAlert('Kateqoriya uğurla silindi', 'success');
                this.loadCategories();
            } else {
                const error = await response.json();
                this.showAlert(error.message || 'Kateqoriya silinərkən xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Error deleting category:', error);
            this.showAlert('Kateqoriya silinərkən xəta baş verdi', 'error');
        }
    }

    showAlert(message, type = 'info') {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.style.minWidth = '300px';
        
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }
}

// Initialize when DOM is loaded
let categoriesManager;
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing CategoriesManager');
    categoriesManager = new CategoriesManager();
});