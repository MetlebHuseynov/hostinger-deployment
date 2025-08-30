class BrandsManager {
    constructor() {
        this.brands = [];
        this.init();
    }

    init() {
        console.log('BrandsManager initialized');
        this.bindEvents();
        this.loadBrands();
    }

    bindEvents() {
        // Add brand button
        const addBtn = document.getElementById('add-brand-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.openModal());
        }

        // Modal close events
        const closeBtn = document.querySelector('#brand-modal .btn-close');
        const cancelBtn = document.getElementById('cancel-btn');
        const modal = document.getElementById('brand-modal');
        
        if (closeBtn) closeBtn.addEventListener('click', () => this.closeModal());
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.closeModal());
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModal();
            });
        }

        // Form submit
        const form = document.getElementById('brand-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    async loadBrands() {
        try {
            const response = await fetch('/api/markas');
            if (response.ok) {
                this.brands = await response.json();
                this.renderBrands();
            } else {
                this.showAlert('Brendlər yüklənə bilmədi', 'error');
            }
        } catch (error) {
            console.error('Error loading brands:', error);
            this.showAlert('Brendlər yüklənərkən xəta baş verdi', 'error');
        }
    }

    renderBrands() {
        const tbody = document.getElementById('brands-table');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        if (this.brands.length === 0) {
            document.getElementById('no-data').classList.remove('d-none');
            return;
        }
        
        document.getElementById('no-data').classList.add('d-none');
        
        this.brands.forEach(brand => {
            const row = document.createElement('tr');
            const imageHtml = brand.image ? 
                `<img src="${brand.image}" alt="${brand.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">` : 
                '<i class="fas fa-image text-muted"></i>';
            
            const statusBadge = brand.status === 'active' ? 
                '<span class="badge bg-success">Aktiv</span>' : 
                '<span class="badge bg-secondary">Qeyri-aktiv</span>';
            
            const createdDate = brand.created_at ? 
                new Date(brand.created_at).toLocaleDateString('az-AZ') : 
                'Məlum deyil';
            
            row.innerHTML = `
                <td>${brand.id}</td>
                <td>${imageHtml}</td>
                <td>${brand.name}</td>
                <td>${brand.description || 'Təsvir yoxdur'}</td>
                <td>${brand.website ? `<a href="${brand.website}" target="_blank">${brand.website}</a>` : 'Yoxdur'}</td>
                <td>${brand.product_count || 0}</td>
                <td>${statusBadge}</td>
                <td>${createdDate}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="brandsManager.editBrand(${brand.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="brandsManager.deleteBrand(${brand.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    openModal(brandId = null) {
        const modal = document.getElementById('brand-modal');
        const form = document.getElementById('brand-form');
        const title = document.getElementById('modal-title');
        
        if (!modal || !form) {
            console.error('Modal elements not found');
            return;
        }

        // Reset form
        form.reset();
        
        if (brandId) {
            // Edit mode
            const brand = this.brands.find(b => b.id === brandId);
            if (brand) {
                document.getElementById('brand-id').value = brand.id;
                document.getElementById('brand-name').value = brand.name;
                document.getElementById('brand-description').value = brand.description || '';
                document.getElementById('brand-status').value = brand.status || 'active';
                const websiteField = document.getElementById('marka-website');
                if (websiteField) websiteField.value = brand.website || '';
                if (title) title.textContent = 'Brendi Redaktə Et';
            }
        } else {
            // Add mode
            document.getElementById('brand-id').value = '';
            if (title) title.textContent = 'Yeni Brend Əlavə Et';
        }

        modal.style.display = 'block';
        modal.classList.add('show');
        
        // Focus first input
        const firstInput = form.querySelector('input[type="text"]');
        if (firstInput) firstInput.focus();
    }

    closeModal() {
        const modal = document.getElementById('brand-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const brandData = {
            name: formData.get('name'),
            description: formData.get('description'),
            status: formData.get('status') || 'active',
            website: formData.get('website') || null
        };
        
        // Check if we have a hidden brand ID field for editing
        const brandIdField = document.getElementById('brand-id');
        const brandId = brandIdField ? brandIdField.value : null;
        const isEdit = brandId && brandId !== '';
        
        try {
            const url = isEdit ? `/api/markas/${brandId}` : '/api/markas';
            const method = isEdit ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(brandData)
            });
            
            if (response.ok) {
                this.showAlert(isEdit ? 'Brend uğurla yeniləndi' : 'Brend uğurla əlavə edildi', 'success');
                this.closeModal();
                this.loadBrands();
            } else {
                const error = await response.json();
                this.showAlert(error.message || 'Xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Error saving brand:', error);
            this.showAlert('Brend saxlanılarkən xəta baş verdi', 'error');
        }
    }

    editBrand(brandId) {
        this.openModal(brandId);
    }

    async deleteBrand(brandId) {
        if (!confirm('Bu brendi silmək istədiyinizə əminsiniz?')) {
            return;
        }
        
        try {
            const response = await fetch(`/api/markas/${brandId}`, {
                method: 'DELETE'
            });
            
            if (response.ok) {
                this.showAlert('Brend uğurla silindi', 'success');
                this.loadBrands();
            } else {
                const error = await response.json();
                this.showAlert(error.message || 'Brend silinərkən xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Error deleting brand:', error);
            this.showAlert('Brend silinərkən xəta baş verdi', 'error');
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
let brandsManager;
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing BrandsManager');
    brandsManager = new BrandsManager();
});}}}