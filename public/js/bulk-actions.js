/**
 * Bulk Actions Component for Twinx ERP
 * Provides reusable bulk selection, actions, and operations
 */

class BulkActions {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.options = {
            actions: options.actions || [],
            onAction: options.onAction || (() => { }),
            ...options
        };
        this.selectedIds = new Set();
        this.init();
    }

    init() {
        this.wrapTable();
        this.addCheckboxes();
        this.renderToolbar();
        this.bindEvents();
    }

    wrapTable() {
        // Add bulk action toolbar before table
        const toolbar = document.createElement('div');
        toolbar.id = 'bulkToolbar';
        toolbar.className = 'bulk-toolbar card border-0 shadow-sm mb-3 d-none';
        this.table.parentNode.insertBefore(toolbar, this.table);
    }

    addCheckboxes() {
        // Add header checkbox
        const headerRow = this.table.querySelector('thead tr');
        const th = document.createElement('th');
        th.innerHTML = '<input type="checkbox" class="form-check-input" id="selectAll">';
        th.style.width = '40px';
        headerRow.insertBefore(th, headerRow.firstChild);

        // Add row checkboxes
        this.table.querySelectorAll('tbody tr').forEach(row => {
            const td = document.createElement('td');
            const id = row.dataset.id || row.querySelector('[data-id]')?.dataset.id || '';
            td.innerHTML = `<input type="checkbox" class="form-check-input row-checkbox" value="${id}">`;
            row.insertBefore(td, row.firstChild);
        });
    }

    renderToolbar() {
        const toolbar = document.getElementById('bulkToolbar');
        toolbar.innerHTML = `
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary me-2" id="selectedCount">0</span>
                        <span class="text-muted">عنصر محدد</span>
                    </div>
                    <div class="btn-group">
                        ${this.renderActionButtons()}
                    </div>
                </div>
            </div>
        `;
    }

    renderActionButtons() {
        const defaultActions = [
            { id: 'delete', label: 'حذف', icon: 'trash', class: 'btn-danger' },
            { id: 'export', label: 'تصدير', icon: 'download', class: 'btn-success' },
            { id: 'activate', label: 'تفعيل', icon: 'check-circle', class: 'btn-outline-success' },
            { id: 'deactivate', label: 'تعطيل', icon: 'x-circle', class: 'btn-outline-warning' },
        ];

        const actions = this.options.actions.length > 0 ? this.options.actions : defaultActions;

        return actions.map(action => `
            <button class="btn btn-sm ${action.class}" onclick="bulkActions.executeAction('${action.id}')">
                <i class="bi bi-${action.icon} me-1"></i>
                ${action.label}
            </button>
        `).join('');
    }

    bindEvents() {
        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', (e) => {
            this.table.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
                if (e.target.checked) {
                    this.selectedIds.add(cb.value);
                } else {
                    this.selectedIds.delete(cb.value);
                }
            });
            this.updateToolbar();
        });

        // Individual row checkboxes
        this.table.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.selectedIds.add(e.target.value);
                } else {
                    this.selectedIds.delete(e.target.value);
                }
                this.updateToolbar();
            });
        });
    }

    updateToolbar() {
        const toolbar = document.getElementById('bulkToolbar');
        const count = this.selectedIds.size;

        toolbar.classList.toggle('d-none', count === 0);
        document.getElementById('selectedCount').textContent = count;
    }

    executeAction(actionId) {
        if (this.selectedIds.size === 0) {
            alert('يرجى تحديد عناصر أولاً');
            return;
        }

        const ids = Array.from(this.selectedIds);

        if (actionId === 'delete') {
            if (!confirm(`هل أنت متأكد من حذف ${ids.length} عنصر؟`)) return;
        }

        this.options.onAction(actionId, ids);
    }

    getSelectedIds() {
        return Array.from(this.selectedIds);
    }

    clearSelection() {
        this.selectedIds.clear();
        this.table.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        this.updateToolbar();
    }
}

// Export for use
window.BulkActions = BulkActions;

// Helper functions for common bulk operations
window.bulkDelete = async function (url, ids) {
    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ ids })
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    } catch (e) {
        alert('حدث خطأ في الاتصال');
    }
};

window.bulkExport = function (url, ids, format = 'excel') {
    const params = new URLSearchParams({ ids: ids.join(','), format });
    window.location.href = `${url}?${params}`;
};

window.bulkUpdate = async function (url, ids, data) {
    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ ids, ...data })
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'حدث خطأ');
        }
    } catch (e) {
        alert('حدث خطأ في الاتصال');
    }
};
