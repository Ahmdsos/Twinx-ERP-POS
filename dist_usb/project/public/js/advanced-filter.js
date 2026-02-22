/**
 * Advanced Filter Component for Twinx ERP
 * Provides reusable advanced filtering, saved filters, and quick filters
 */

class AdvancedFilter {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            storageKey: options.storageKey || 'filters_' + containerId,
            onFilter: options.onFilter || (() => {}),
            fields: options.fields || [],
            ...options
        };
        this.currentFilters = {};
        this.savedFilters = this.loadSavedFilters();
        this.init();
    }

    init() {
        this.render();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="advanced-filter card border-0 shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>فلترة متقدمة</h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="advancedFilter.togglePanel()">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body filter-panel" style="display:none;">
                    <div class="row g-3" id="filterFields">
                        ${this.renderFields()}
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <button class="btn btn-primary btn-sm" onclick="advancedFilter.applyFilters()">
                                <i class="bi bi-search me-1"></i>تطبيق
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="advancedFilter.clearFilters()">
                                <i class="bi bi-x-circle me-1"></i>مسح
                            </button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline-success btn-sm" onclick="advancedFilter.saveFilter()">
                                <i class="bi bi-save me-1"></i>حفظ الفلتر
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-bookmark me-1"></i>الفلاتر المحفوظة
                                </button>
                                <ul class="dropdown-menu" id="savedFiltersList">
                                    ${this.renderSavedFilters()}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 quick-filters">
                    <small class="text-muted me-2">فلترة سريعة:</small>
                    ${this.renderQuickFilters()}
                </div>
            </div>
        `;
    }

    renderFields() {
        return this.options.fields.map(field => `
            <div class="col-md-${field.width || 3}">
                <label class="form-label">${field.label}</label>
                ${this.renderFieldInput(field)}
            </div>
        `).join('');
    }

    renderFieldInput(field) {
        const value = this.currentFilters[field.name] || '';
        switch (field.type) {
            case 'select':
                return `
                    <select class="form-select form-select-sm" name="${field.name}" data-filter>
                        <option value="">الكل</option>
                        ${field.options.map(o => `<option value="${o.value}" ${value == o.value ? 'selected' : ''}>${o.label}</option>`).join('')}
                    </select>
                `;
            case 'date':
                return `<input type="date" class="form-control form-control-sm" name="${field.name}" value="${value}" data-filter>`;
            case 'dateRange':
                return `
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control" name="${field.name}_from" placeholder="من" data-filter>
                        <input type="date" class="form-control" name="${field.name}_to" placeholder="إلى" data-filter>
                    </div>
                `;
            case 'number':
                return `<input type="number" class="form-control form-control-sm" name="${field.name}" value="${value}" placeholder="${field.placeholder || ''}" data-filter>`;
            default:
                return `<input type="text" class="form-control form-control-sm" name="${field.name}" value="${value}" placeholder="${field.placeholder || ''}" data-filter>`;
        }
    }

    renderQuickFilters() {
        const quickFilters = this.options.quickFilters || [
            { label: 'اليوم', filters: { date_from: new Date().toISOString().split('T')[0], date_to: new Date().toISOString().split('T')[0] } },
            { label: 'هذا الأسبوع', filters: { date_from: this.getWeekStart(), date_to: new Date().toISOString().split('T')[0] } },
            { label: 'هذا الشهر', filters: { date_from: this.getMonthStart(), date_to: new Date().toISOString().split('T')[0] } },
        ];

        return quickFilters.map(qf => `
            <button class="btn btn-outline-secondary btn-sm me-1" onclick="advancedFilter.applyQuickFilter(${JSON.stringify(qf.filters).replace(/"/g, '&quot;')})">
                ${qf.label}
            </button>
        `).join('');
    }

    renderSavedFilters() {
        if (this.savedFilters.length === 0) {
            return '<li><span class="dropdown-item text-muted">لا توجد فلاتر محفوظة</span></li>';
        }
        return this.savedFilters.map((sf, index) => `
            <li>
                <a class="dropdown-item d-flex justify-content-between align-items-center" href="#" onclick="advancedFilter.loadSavedFilter(${index})">
                    ${sf.name}
                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="event.stopPropagation(); advancedFilter.deleteSavedFilter(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </a>
            </li>
        `).join('');
    }

    bindEvents() {
        // Auto-filter on enter key
        this.container.querySelectorAll('[data-filter]').forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.applyFilters();
            });
        });
    }

    togglePanel() {
        const panel = this.container.querySelector('.filter-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    applyFilters() {
        this.currentFilters = {};
        this.container.querySelectorAll('[data-filter]').forEach(input => {
            if (input.value) {
                this.currentFilters[input.name] = input.value;
            }
        });
        this.options.onFilter(this.currentFilters);
    }

    clearFilters() {
        this.container.querySelectorAll('[data-filter]').forEach(input => {
            input.value = '';
        });
        this.currentFilters = {};
        this.options.onFilter({});
    }

    applyQuickFilter(filters) {
        Object.entries(filters).forEach(([name, value]) => {
            const input = this.container.querySelector(`[name="${name}"]`);
            if (input) input.value = value;
        });
        this.applyFilters();
    }

    saveFilter() {
        const name = prompt('اسم الفلتر:');
        if (!name) return;
        
        this.applyFilters();
        this.savedFilters.push({ name, filters: { ...this.currentFilters } });
        localStorage.setItem(this.options.storageKey, JSON.stringify(this.savedFilters));
        this.render();
    }

    loadSavedFilter(index) {
        const sf = this.savedFilters[index];
        if (!sf) return;
        
        Object.entries(sf.filters).forEach(([name, value]) => {
            const input = this.container.querySelector(`[name="${name}"]`);
            if (input) input.value = value;
        });
        this.applyFilters();
    }

    deleteSavedFilter(index) {
        if (!confirm('حذف هذا الفلتر؟')) return;
        this.savedFilters.splice(index, 1);
        localStorage.setItem(this.options.storageKey, JSON.stringify(this.savedFilters));
        this.render();
    }

    loadSavedFilters() {
        try {
            return JSON.parse(localStorage.getItem(this.options.storageKey)) || [];
        } catch {
            return [];
        }
    }

    getWeekStart() {
        const d = new Date();
        d.setDate(d.getDate() - d.getDay());
        return d.toISOString().split('T')[0];
    }

    getMonthStart() {
        const d = new Date();
        d.setDate(1);
        return d.toISOString().split('T')[0];
    }
}

// Export for use
window.AdvancedFilter = AdvancedFilter;
