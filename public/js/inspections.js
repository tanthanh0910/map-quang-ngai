// Inspection page JavaScript
let totalQuantity = window.inspectionData.totalQuantity;
let passQuantity = window.inspectionData.passQuantity;
let failQuantity = window.inspectionData.failQuantity;
// Unique client id used so we can ignore socket broadcasts emitted by ourselves
const inspectionClientId = (function generateClientId() {
    try {
        // Try to use crypto if available for better uniqueness
        if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID();
    } catch (e) { }
    return 'client_' + Math.random().toString(36).substr(2, 9);
})();
window.inspectionClientId = inspectionClientId;
let selectedDefects = [];
let defectModal = null;
let currentPage = 1;
let filterResult = '';
let defectFrequencies = {}; // Store defect usage frequencies for this session

const socket = io(window.inspectionData.realtimeServerUrl);
const room = `line.${window.inspectionData.lineId}.plan_daily.${window.inspectionData.dailyPlanId}`;
socket.emit('join', room);

// Socket updates are the canonical source of truth for clients that didn't initiate an action.
// The client that performs an action (click pass/fail/delete) will update its UI immediately
// from the API response; it will also emit a socket event which other clients will use to
// update their UI. To prevent duplicate updates, the emitter ignores socket events that
// came from itself (using inspectionClientId).
socket.on('inspection.updated', function (message) {
    console.log('Socket inspection.updated received on inspection page:', message);
    if (!message || !message.data) return;
    const d = message.data;
    // Ignore messages originating from this client (we already updated UI locally)
    if (message.clientId && message.clientId === inspectionClientId) {
        console.debug('Ignoring socket update from self (clientId=' + inspectionClientId + ')');
        return;
    }

    // Update global counters (used by updateDisplay)
    if (d.target_quantity !== undefined) totalQuantity = d.target_quantity;
    if (d.pass_quantity !== undefined) passQuantity = d.pass_quantity;
    if (d.fail_quantity !== undefined) failQuantity = d.fail_quantity;

    // Update inspection count display elements
    updateDisplay();

    // If socket payload includes the latest history page, render it and update recent defects
    if (d.history && d.history.data) {
        try {
            // Render the latest history page (replaces table contents)
            renderHistoryFromApi(d.history);

            // Recompute defect frequencies from history data
            // Initialize counts
            const newFrequencies = {};
            d.history.data.forEach(item => {
                if (item.defect_ids && Array.isArray(item.defect_ids)) {
                    item.defect_ids.forEach(id => {
                        newFrequencies[id] = (newFrequencies[id] || 0) + 1;
                    });
                }
            });
            // Replace session frequencies for consistency (socket is canonical)
            defectFrequencies = newFrequencies;
            // Update quick-defect-list UI to reflect current top 5
            updateQuickDefects();

            // // Optionally update a '#recent-defect-list' if present (show top 5 names)
            // const recentDefectListEl = document.getElementById('recent-defect-list');
            // if (recentDefectListEl) {
            //     // Build top N by frequency
            //     const pairs = Object.entries(defectFrequencies).map(([id, count]) => ({ id: parseInt(id), count }));
            //     pairs.sort((a, b) => b.count - a.count);
            //     const top = pairs.slice(0, 5);
            //     recentDefectListEl.innerHTML = top.map(t => `<div class="recent-defect-item" data-id="${t.id}">${t.count}× ${t.id}</div>`).join('');
            // }
        } catch (e) {
            console.warn('Failed to process history from socket payload', e);
        }
    }
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    initializeModal();
    initializeQuickInspectionModal();
    loadHistory();
    setupEventListeners();
});

// Initialize modal
function initializeModal() {
    const modalElement = document.getElementById('defectModal');
    if (!modalElement) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        defectModal = new bootstrap.Modal(modalElement);
    } else {
        defectModal = createManualModal(modalElement);
    }

    // Ensure close buttons work
    const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (defectModal && defectModal.hide) {
                defectModal.hide();
            }
        });
    });

    // Clear selections and update quick defects when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function () {
        console.log('Modal hidden event fired, updating quick defects, resetting isQuickInspection');
        clearDefectSelections();
        sortDefectsByFrequency(); // Update quick defects order
        // Reset quick inspection flag
        isQuickInspection = false;
    });
    // For manual modal, listen to our custom hide
    if (!defectModal._isBootstrap) {
        // Override hide to clear selections and update quick defects
        const originalHide = defectModal.hide;
        defectModal.hide = function () {
            clearDefectSelections();
            sortDefectsByFrequency(); // Update quick defects order
            isQuickInspection = false;
            originalHide.call(this);
        };
    }
}

// Initialize quick inspection modal
function initializeQuickInspectionModal() {
    const modalElement = document.getElementById('quickInspectionModal');
    if (modalElement) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            quickInspectionModal = new bootstrap.Modal(modalElement);
        } else {
            quickInspectionModal = createManualModal(modalElement);
        }
        // Reset modal when hidden
        modalElement.addEventListener('hidden.bs.modal', resetQuickInspectionModal);
    }
}

// Clear all defect selections and highlights
function clearDefectSelections() {
    document.querySelectorAll('.defect-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.defect-card.selected').forEach(card => {
        card.classList.remove('selected');
    });
    // Also clear quick defects
    document.querySelectorAll('.quick-defect-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.quick-defect-card.selected').forEach(card => {
        card.classList.remove('selected');
    });
}

// Manual modal implementation
function createManualModal(element) {
    return {
        show: function () {
            element.classList.add('show');
            element.style.display = 'block';
            document.body.classList.add('modal-open');
            // Create backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
            setTimeout(() => backdrop.classList.add('show'), 10);
        },
        hide: function () {
            element.classList.remove('show');
            setTimeout(() => {
                element.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.getElementById('modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('show');
                    setTimeout(() => backdrop.remove(), 150);
                }
                // Dispatch hidden event for manual modal
                element.dispatchEvent(new Event('hidden.bs.modal'));
            }, 150);
        }
    };
}

// Manual sidebar implementation
function createManualSidebar(element) {
    console.log('Using manual sidebar toggle');
    return {
        show: function () {
            element.classList.add('show');
            const backdrop = document.createElement('div');
            backdrop.className = 'offcanvas-backdrop fade';
            backdrop.id = 'sidebar-backdrop';
            backdrop.onclick = () => this.hide();
            document.body.appendChild(backdrop);
            setTimeout(() => backdrop.classList.add('show'), 10);
            document.body.classList.add('offcanvas-open');
        },
        hide: function () {
            element.classList.remove('show');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (backdrop) backdrop.classList.remove('show');
            setTimeout(() => {
                if (backdrop) backdrop.remove();
                document.body.classList.remove('offcanvas-open');
            }, 300);
        }
    };
}

// Setup event listeners
function setupEventListeners() {
    // Filter change
    // document.getElementById('filter-result').addEventListener('change', function (e) {
    //     filterResult = e.target.value;
    //     currentPage = 1;
    //     loadHistory();
    // });

    // Pass button
    document.getElementById('btn-pass').addEventListener('click', handlePass);

    // Fail button - opens full modal
    document.getElementById('btn-fail').addEventListener('click', () => {
        sortDefectsByFrequency(); // Ensure defects are sorted by frequency
        if (defectModal) defectModal.show();
    });

    // Quick defect card selection
    document.addEventListener('click', function (e) {
        if (e.target.closest('.quick-defect-card')) {
            const card = e.target.closest('.quick-defect-card');
            const checkbox = card.querySelector('.quick-defect-checkbox');

            // Toggle selection
            checkbox.checked = !checkbox.checked;
            card.classList.toggle('selected');
        }
    });

    // Quick submit button
    document.getElementById('btn-quick-submit').addEventListener('click', handleQuickSubmit);

    // Save defects
    document.getElementById('btn-save-defects').addEventListener('click', handleSaveDefects);

    // Quick inspection button
    document.getElementById('btn-quick-inspection').addEventListener('click', function () {
        console.log('Quick inspection button clicked');
        if (quickInspectionModal) {
            quickInspectionModal.show();
        } else {
            console.log('Modal not initialized');
        }
    });

    // Quick inspection submit
    document.getElementById('btn-quick-inspection-submit').addEventListener('click', handleQuickInspectionSubmit);

    // Quick inspection result change
    document.querySelectorAll('input[name="quick-result"]').forEach(radio => {
        radio.addEventListener('change', function () {
            quickResult = this.value;
            const defectSection = document.getElementById('quick-defect-section');
            const clearAllBtn = document.getElementById('btn-quick-clear-all');
            if (this.value === 'fail') {
                defectSection.classList.remove('d-none');
                clearAllBtn.classList.remove('d-none');
            } else {
                defectSection.classList.add('d-none');
                clearAllBtn.classList.add('d-none');
            }
        });
    });

    // Quick inspection quantity change
    document.getElementById('quick-quantity').addEventListener('input', function () {
        quickQuantity = parseInt(this.value) || 1;
    });

    // Quick inspection cancel
    document.getElementById('btn-quick-inspection-cancel').addEventListener('click', function () {
        if (quickInspectionModal) {
            quickInspectionModal.hide();
        }
    });

    // Quick inspection close
    document.getElementById('quick-inspection-close').addEventListener('click', function () {
        if (quickInspectionModal) {
            quickInspectionModal.hide();
        }
    });

    // Quick defect search
    document.getElementById('quick-defect-search').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('#quick-defect-list-modal .defect-item').forEach(item => {
            const name = item.querySelector('.defect-name').textContent.toLowerCase();
            item.style.display = name.includes(searchTerm) ? 'block' : 'none';
        });
    });

    // Quick defect card selection
    document.addEventListener('click', function (e) {
        if (e.target.closest('#quick-defect-list-modal .defect-card')) {
            const card = e.target.closest('.defect-card');
            const checkbox = card.querySelector('.defect-checkbox');

            // Toggle selection
            checkbox.checked = !checkbox.checked;
            card.classList.toggle('selected', checkbox.checked);
        }
    });

    // Quick clear all
    document.getElementById('btn-quick-clear-all').addEventListener('click', () => {
        document.querySelectorAll('#quick-defect-list-modal .defect-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('#quick-defect-list-modal .defect-card.selected').forEach(card => card.classList.remove('selected'));
    });

    // Clear all
    document.getElementById('btn-clear-all').addEventListener('click', () => {
        // Clear modal defects
        document.querySelectorAll('.defect-checkbox').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.defect-card');
            card.classList.remove('selected');
        });
        // Clear quick defects
        document.querySelectorAll('.quick-defect-checkbox').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.quick-defect-card');
            card.classList.remove('selected');
        });
    });

    // Search defects
    document.getElementById('defect-search').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.defect-item').forEach(item => {
            const name = item.querySelector('.defect-name').textContent.toLowerCase();
            item.style.display = name.includes(searchTerm) ? 'block' : 'none';
        });
    });

    // Defect card selection
    document.addEventListener('click', function (e) {
        if (e.target.closest('#defect-list .defect-card')) {
            const card = e.target.closest('.defect-card');
            const checkbox = card.querySelector('.defect-checkbox');

            // Toggle selection
            checkbox.checked = !checkbox.checked;
            card.classList.toggle('selected');
        }
    });
}

// Update display
function updateDisplay() {
    const totalElement = document.getElementById('total-quantity');
    const currentElement = document.getElementById('current-quantity');
    const passElement = document.getElementById('pass-quantity');
    const failElement = document.getElementById('fail-quantity');

    // Animate if value changed
    if (parseInt(totalElement.textContent) !== totalQuantity) {
        totalElement.classList.add('animate-jump');
        setTimeout(() => totalElement.classList.remove('animate-jump'), 500);
    }
    totalElement.textContent = totalQuantity;

    const currentQuantity = passQuantity + failQuantity;
    if (parseInt(currentElement.textContent) !== currentQuantity) {
        currentElement.classList.add('animate-jump');
        setTimeout(() => currentElement.classList.remove('animate-jump'), 500);
    }
    currentElement.textContent = currentQuantity;

    if (parseInt(passElement.textContent) !== passQuantity) {
        passElement.classList.add('animate-jump');
        setTimeout(() => passElement.classList.remove('animate-jump'), 500);
    }
    passElement.textContent = passQuantity;

    if (parseInt(failElement.textContent) !== failQuantity) {
        failElement.classList.add('animate-jump');
        setTimeout(() => failElement.classList.remove('animate-jump'), 500);
    }
    failElement.textContent = failQuantity;

    // After updating values, check if we reached target and toggle UI accordingly
    checkIfTargetReached();
}

// Check if current quantity has reached the total/target and update UI
function checkIfTargetReached() {
    const totalElement = document.getElementById('total-quantity');
    const currentElement = document.getElementById('current-quantity');
    if (!totalElement || !currentElement) return;

    const total = parseInt(totalElement.textContent) || 0;
    const current = parseInt(currentElement.textContent) || 0;

    // Buttons + containers
    const passBtn = document.getElementById('btn-pass');
    const failBtn = document.getElementById('btn-fail');
    const saveBtn = document.getElementById('btn-save-defects');
    const quickSubmitBtn = document.getElementById('btn-quick-submit');
    const actionContainer = document.getElementById('inspection-actions');
    const completeContainer = document.getElementById('inspection-complete');

    // We'll use the dedicated completion card for message. If a separate notice element exists, we will use it.
    const noticeElement = document.getElementById('inspection-limit-notice');

    // No counts for the overlay; just show/hide it

    if (current >= total && total > 0) {
        // Hide actions container (whole Pass/Fail + Quick Defects)
        if (actionContainer) actionContainer.classList.add('d-none');
        // Show the complete message
        if (completeContainer) {
            completeContainer.classList.remove('d-none');
            completeContainer.classList.add('show');
            // Prevent scrolling in background
            document.body.classList.add('modal-open');
        }

        // No counts to update on the overlay per updated UI

        // Small inline notice (optional)
        // Ensure the small inline notice is hidden when showing the full completion card
        if (noticeElement) noticeElement.classList.add('d-none');
    } else {
        // Show the actions container again
        if (actionContainer) actionContainer.classList.remove('d-none');
        // Hide the complete message
        if (completeContainer) {
            completeContainer.classList.add('d-none');
            completeContainer.classList.remove('show');
            // Re-enable scrolling
            document.body.classList.remove('modal-open');
        }

        if (passBtn) passBtn.classList.remove('d-none');
        if (failBtn) failBtn.disabled = false;
        if (saveBtn) saveBtn.disabled = false;
        if (quickSubmitBtn) quickSubmitBtn.disabled = false;
        if (noticeElement) {
            noticeElement.textContent = '';
            noticeElement.classList.add('d-none');
        }
    }
}

// Show a small temporary inline notice (instead of alert)
function showLimitNotice(message) {
    console.log('Showing limit notice:', message);
    const notice = document.getElementById('inspection-limit-notice');
    if (!notice) {
        alert(message);
        return;
    }
    const noticeText = document.getElementById('inspection-limit-notice-text');
    if (noticeText) noticeText.textContent = message;
    notice.classList.remove('d-none');
    // Auto-hide after 3 seconds
    clearTimeout(window._inspectionNoticeTimeout);
    window._inspectionNoticeTimeout = setTimeout(() => {
        notice.classList.add('d-none');
    }, 3000);
}

// Load history
async function loadHistory(page = 1, updateCounts = false) {
    if (!window.inspectionData.lineId) return;

    try {
        let url = `/inspections-history/${window.inspectionData.lineId}?production_plan_id=${window.inspectionData.productionPlanId}&page=${page}`;
        console.log('Loading history from URL:', url);
        if (filterResult) url += `&result=${filterResult}`;

        const result = await apiCall(url, 'GET');
        if (result.success && result.data) {
            // Optionally update counts if requested (e.g. emitter wants immediate refresh)
            if (updateCounts) {
                if (result.data.pass_quantity !== undefined) passQuantity = result.data.pass_quantity;
                if (result.data.fail_quantity !== undefined) failQuantity = result.data.fail_quantity;
                if (result.data.total_quantity !== undefined) totalQuantity = result.data.total_quantity;
                updateDisplay();
            }
            renderHistoryFromApi(result.data);
        }
    } catch (error) {
        console.error('Failed to load history:', error);
    }
}

// Render history
function renderHistoryFromApi(paginationData) {
    const tbody = document.getElementById('history-table');
    const paginationWrapper = document.getElementById('history-pagination');
    const paginationUl = paginationWrapper.querySelector('.pagination');

    if (!paginationData.data || paginationData.data.length === 0) {
        tbody.innerHTML = '<tr class="text-center text-muted"><td colspan="6" class="py-3 inspection-text">Chưa có lịch sử kiểm định</td></tr>';
        paginationWrapper.style.display = 'none';
        return;
    }

    tbody.innerHTML = paginationData.data.map(item => {
        const time = item.inspected_at ? new Date(item.inspected_at).toLocaleTimeString('vi-VN', {
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        }) : '';
        const inspector = item.user ? item.user.name : 'N/A';
        return `
            <tr class="align-middle">
                <td class="py-3 inspection-text">${time}</td>
                <td class="py-3 inspection-text">${inspector}</td>
                <td class="py-3 inspection-text">
                    ${item.result === 'pass' ? '<span class="badge bg-success">Đạt</span>' : '<span class="badge bg-danger">Lỗi</span>'}
                </td>
                <td class="py-3 inspection-text">${item.quantity ? item.quantity : 1}</td>
                <td class="py-3 inspection-text">
                    ${item.defect_names ? `<small class="text-muted">${item.defect_names}</small>` : ''}
                </td>
                <td class="py-3 text-end inspection-text">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="deleteHistoryItem(${item.id}, this)">
                        <i class="bi bi-trash icon-lg"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    if (paginationData.last_page > 1) {
        paginationWrapper.style.display = 'block';
        paginationUl.innerHTML = renderPagination(paginationData);
    } else {
        paginationWrapper.style.display = 'none';
    }
}

// Render pagination
function renderPagination(data) {
    let html = '';

    // Previous
    if (data.current_page === 1) {
        html += `<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left me-1"></i>Trang trước</span></li>`;
    } else {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${data.current_page - 1}); return false;"><i class="bi bi-chevron-left me-1"></i>Trang trước</a></li>`;
    }

    // Pages
    const maxPages = 5;
    let startPage = Math.max(1, data.current_page - Math.floor(maxPages / 2));
    let endPage = Math.min(data.last_page, startPage + maxPages - 1);
    if (endPage - startPage < maxPages - 1) startPage = Math.max(1, endPage - maxPages + 1);

    for (let i = startPage; i <= endPage; i++) {
        if (i === data.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;
        } else if (i === data.current_page - 2 || i === data.current_page + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Next
    if (data.current_page === data.last_page) {
        html += `<li class="page-item disabled"><span class="page-link">Trang sau<i class="bi bi-chevron-right ms-1"></i></span></li>`;
    } else {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${data.current_page + 1}); return false;">Trang sau<i class="bi bi-chevron-right ms-1"></i></a></li>`;
    }

    return html;
}

// Change page
function changePage(page) {
    currentPage = page;
    loadHistory(page);
}

// Delete history
async function deleteHistoryItem(historyId, button) {
    if (!confirm('Bạn có chắc chắn muốn xóa lịch sử này?')) return;

    // Disable button while request is in flight
    button.disabled = true;

    try {
        const result = await apiCall(`/inspections-history/${historyId}`, 'DELETE');
        if (result.success) {
            if (result.data) {
                // Do not set UI counts locally; rely on socket event to update UI.
            }
            // Emit update event via socket (and include history)
            // Update local history UI immediately and update counts by reloading.
            await loadHistory(currentPage, true);
            await emitInspectionUpdate({ data: result.data });
        }
    } catch (error) {
        console.error('Failed to delete history:', error);
    } finally {
        // Restore button state
        button.disabled = false;
    }
}

// NOTE: Loading UI removed — we only disable/enable buttons during operations.

// Handle pass
async function handlePass(event) {
    const button = event && event.currentTarget ? event.currentTarget : this;
    if (!window.inspectionData.dailyPlanId) {
        alert('Không tìm thấy kế hoạch ngày');
        return;
    }

    // Prevent when target reached
    const totalQty = totalQuantity;
    const currentQty = passQuantity + failQuantity;
    if (totalQty > 0 && currentQty >= totalQty) {
        showLimitNotice('Đã đạt tổng SL, không thể kiểm định thêm.');
        return;
    }

    // Disable button while request is in flight
    button.disabled = true;

    try {

        const qtyInput = document.getElementById('inspection-quantity');
        const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;
        // Quick client side check against target
        if (totalQty > 0 && (currentQty + qty) > totalQty) {
            showLimitNotice('Số lượng kiểm định vượt quá tổng SL còn lại.');
            return;
        }
        const result = await apiCall(`/inspections-pass/${window.inspectionData.lineId}`, 'POST', {
            daily_plan_id: window.inspectionData.dailyPlanId,
            quantity: qty,
        });

        if (result.success && result.data) {
            // Update local UI immediately using API response
            updateLocalStateFromApi(result.data);
            // Broadcast update for other clients
            await emitInspectionUpdate({ data: result.data });
        }
    } catch (error) {
        console.error('Failed to record pass:', error);
    } finally {
        button.disabled = false;
    }
}

// Handle save defects
async function handleSaveDefects(event) {
    const button = event && event.currentTarget ? event.currentTarget : this;
    const checkedBoxes = document.querySelectorAll('.defect-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Vui lòng chọn ít nhất một loại lỗi');
        return;
    }

    // Prevent when target reached
    const totalQty = totalQuantity;
    const currentQty = passQuantity + failQuantity;
    const qty = isQuickInspection ? quickQuantity : 1;
    console.log('handleSaveDefects: isQuickInspection=', isQuickInspection, 'quickQuantity=', quickQuantity, 'qty=', qty);
    if (totalQty > 0 && currentQty + qty > totalQty) {
        showLimitNotice('Đã đạt tổng SL, không thể kiểm định thêm.');
        return;
    }

    // Disable button while request is in flight
    button.disabled = true;

    try {
        const defectIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        const result = await apiCall(`/inspections-fail/${window.inspectionData.lineId}`, 'POST', {
            daily_plan_id: window.inspectionData.dailyPlanId,
            defect_ids: defectIds,
            quantity: qty,
        });

        if (result.success && result.data) {
            // Update local UI and history immediately
            updateLocalStateFromApi(result.data);
            // Do not update local counters here; socket payload will include history

            // Update defect frequencies in memory
            updateDefectFrequencies(defectIds, qty);
            // Re-sort defects by updated frequency
            sortDefectsByFrequency();

            // Emit fail event via socket (and include history)
            await emitInspectionUpdate({
                data: result.data,
            });
        }

        if (defectModal) defectModal.hide();
        checkedBoxes.forEach(cb => cb.checked = false);
        // Clear visual selection
        document.querySelectorAll('.defect-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
    } catch (error) {
        console.error('Failed to record fail:', error);
    } finally {
        button.disabled = false;
    }
}

// Handle quick submit defects
async function handleQuickSubmit(event) {
    const button = event && event.currentTarget ? event.currentTarget : this;
    const checkedBoxes = document.querySelectorAll('.quick-defect-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Vui lòng chọn ít nhất một loại lỗi');
        return;
    }

    // Prevent when target reached
    const totalQty = totalQuantity;
    const currentQty = passQuantity + failQuantity;
    if (totalQty > 0 && currentQty >= totalQty) {
        showLimitNotice('Đã đạt tổng SL, không thể kiểm định thêm.');
        return;
    }

    // Disable button while request is in flight
    button.disabled = true;

    try {
        const defectIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        const qtyInput = document.getElementById('quick-quantity');
        const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;
        if (totalQty > 0 && (currentQty + qty) > totalQty) {
            showLimitNotice('Số lượng kiểm định vượt quá tổng SL còn lại.');
            return;
        }
        const result = await apiCall(`/inspections-fail/${window.inspectionData.lineId}`, 'POST', {
            daily_plan_id: window.inspectionData.dailyPlanId,
            defect_ids: defectIds,
            quantity: qty,
        });

        if (result.success && result.data) {
            // Update local UI immediately
            updateLocalStateFromApi(result.data);
            // Do not update local counters here; socket payload will include history

            // Update defect frequencies in memory
            updateDefectFrequencies(defectIds, qty);
            // Re-sort defects by updated frequency
            sortDefectsByFrequency();
            // Emit fail event via socket (and include history)
            await emitInspectionUpdate({
                data: result.data,
            });
        }

        // Clear quick defect selections
        checkedBoxes.forEach(cb => cb.checked = false);
        document.querySelectorAll('.quick-defect-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
    } catch (error) {
        console.error('Failed to record quick fail:', error);
    } finally {
        button.disabled = false;
    }
}

async function emitInspectionUpdate(data) {
    try {
        // Attempt to include latest history into the payload so all clients can update history via socket
        const historyResult = await apiCall(`/inspections-history/${window.inspectionData.lineId}?production_plan_id=${window.inspectionData.productionPlanId}&page=1`, 'GET');
        if (historyResult && historyResult.success && historyResult.data) {
            // Attach history page data to payload
            if (!data.data) data.data = {};
            data.data.history = historyResult.data;
        }
    } catch (err) {
        // If fetching history fails, still emit the event with what we have
        console.warn('Failed to fetch history for socket payload, emitting without it', err);
    }
    // Mark the origin client so that it can ignore updates for events it originated.
    data.clientId = inspectionClientId;
    data.room = room;
    socket.emit('inspection.updated', data);
}

// API helper
async function apiCall(url, method = 'GET', data = null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    };

    if (data) options.body = JSON.stringify(data);

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'API call failed');
        return result;
    } catch (error) {
        console.error('API Error:', error);
        alert('Có lỗi xảy ra: ' + error.message);
        throw error;
    }
}

// Update local UI from API/service response data
function updateLocalStateFromApi(data) {
    if (!data) return;
    // If the API provides explicit counts, use them; else leave local counts as-is.
    if (data.pass_quantity !== undefined) passQuantity = data.pass_quantity;
    if (data.fail_quantity !== undefined) failQuantity = data.fail_quantity;
    if (data.target_quantity !== undefined) totalQuantity = data.target_quantity;
    // Update displays
    updateDisplay();

    // If history is included in the payload, render it
    if (data.history && data.history.data) {
        try {
            renderHistoryFromApi(data.history);
        } catch (err) {
            console.warn('Failed to render history from local update', err);
        }
    } else {
        // Otherwise, fetch the current page of history to update the history table
        loadHistory(currentPage);
    }
}

// Update defect frequencies in memory
function updateDefectFrequencies(defectIds, qty = 1) {
    console.log('Updating defect frequencies for IDs:', defectIds, 'qty=', qty);
    defectIds.forEach(id => {
        const key = parseInt(id);
        defectFrequencies[key] = (defectFrequencies[key] || 0) + qty;
    });
    console.log('Updated frequencies:', defectFrequencies);
}

// Sort defects by frequency
function sortDefectsByFrequency() {
    console.log('Sorting defects by frequency:', defectFrequencies);

    // Sort modal defects
    const container = document.getElementById('defect-list');
    if (container) {
        const defectItems = Array.from(container.querySelectorAll('.defect-item'));

        defectItems.sort((a, b) => {
            const aId = parseInt(a.querySelector('.defect-checkbox').value);
            const bId = parseInt(b.querySelector('.defect-checkbox').value);
            const aFreq = defectFrequencies[aId] || 0;
            const bFreq = defectFrequencies[bId] || 0;
            return bFreq - aFreq; // Most frequent first
        });

        // Re-append sorted items
        defectItems.forEach(item => container.appendChild(item));
        console.log('Modal defects sorted');
    }

    // Update quick defects to show top 5 most frequent
    updateQuickDefects();
}

// Update quick defects to show top 5 most frequent defects
function updateQuickDefects() {
    const quickContainer = document.getElementById('quick-defect-list');
    if (!quickContainer) {
        console.log('Quick container not found');
        return;
    }

    // Get all defect items from modal
    const allDefectItems = Array.from(document.querySelectorAll('#defect-list .defect-item'));
    if (allDefectItems.length === 0) {
        console.log('No defect items found in modal');
        return;
    }

    console.log('Updating quick defects with top 5 most frequent');

    // Sort all defects by frequency
    const sortedDefects = allDefectItems.sort((a, b) => {
        const aId = parseInt(a.querySelector('.defect-checkbox').value);
        const bId = parseInt(b.querySelector('.defect-checkbox').value);
        const aFreq = defectFrequencies[aId] || 0;
        const bFreq = defectFrequencies[bId] || 0;
        return bFreq - aFreq; // Most frequent first
    });

    // Take top 5
    const top5Defects = sortedDefects.slice(0, 5);
    console.log('Top 5 defects:', top5Defects.map(item => ({
        id: item.querySelector('.defect-checkbox').value,
        name: item.querySelector('.defect-name').textContent.trim(),
        freq: defectFrequencies[parseInt(item.querySelector('.defect-checkbox').value)] || 0
    })));

    // Clear current quick defects
    quickContainer.innerHTML = '';

    // Add top 5 as quick defects
    top5Defects.forEach(defectItem => {
        const defectId = defectItem.querySelector('.defect-checkbox').value;
        const defectName = defectItem.querySelector('.defect-name').textContent.trim();

        const quickDefectHtml = `
            <div class="col">
                <div class="card h-100 border quick-defect-card" data-defect-id="${defectId}" style="cursor: pointer;">
                    <div class="card-body p-2 d-flex align-items-center">
                        <input class="form-check-input quick-defect-checkbox d-none" type="checkbox" value="${defectId}"
                            id="quick-defect-${defectId}" data-name="${defectName}">
                        <div class="card-title mb-0 flex-grow-1 small defect-name">
                            ${defectName}
                        </div>
                    </div>
                </div>
            </div>
        `;
        quickContainer.insertAdjacentHTML('beforeend', quickDefectHtml);
    });

    console.log('Quick defects updated with top 5');
}

// Initialize defect sorting on page load
document.addEventListener('DOMContentLoaded', function () {
    // Sort defects after a short delay to ensure DOM is ready
    setTimeout(() => {
        sortDefectsByFrequency();
    }, 100);
});

// Reset quick inspection modal
function resetQuickInspectionModal() {
    // Reset radio to pass
    document.getElementById('quick-pass').checked = true;
    // Hide defect section
    document.getElementById('quick-defect-section').classList.add('d-none');
    // Hide clear all button
    document.getElementById('btn-quick-clear-all').classList.add('d-none');
    // Clear defect selections
    document.querySelectorAll('#quick-defect-list-modal .defect-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('#quick-defect-list-modal .defect-card.selected').forEach(card => card.classList.remove('selected'));
    // Reset quantity to 1
    document.getElementById('quick-quantity').value = 1;
    quickQuantity = 1;
    quickResult = 'pass';
}

// Quick Inspection Modal Logic
let quickInspectionModal = null;
let isQuickInspection = false;
let quickQuantity = 1;
let quickResult = 'pass';

// Handle quick inspection submit
async function handleQuickInspectionSubmit(event) {
    const button = event.currentTarget;
    const quantity = parseInt(document.getElementById('quick-quantity').value) || 1;
    const result = document.querySelector('input[name="quick-result"]:checked').value;

    // Prevent when target reached
    const totalQty = totalQuantity;
    const currentQty = passQuantity + failQuantity;
    if (totalQty > 0 && currentQty + quantity > totalQty) {
        showLimitNotice('Không thể vượt quá số lượng mục tiêu.');
        return;
    }

    button.disabled = true;

    try {
        if (result === 'pass') {
            // Submit pass
            await submitQuickPass(quantity);
        } else {
            // Submit fail with selected defects
            const checkedBoxes = document.querySelectorAll('#quick-defect-list-modal .defect-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất một loại lỗi');
                return;
            }
            const defectIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
            const resultApi = await apiCall(`/inspections-fail/${window.inspectionData.lineId}`, 'POST', {
                daily_plan_id: window.inspectionData.dailyPlanId,
                defect_ids: defectIds,
                quantity: quantity,
            });

            if (resultApi.success && resultApi.data) {
                updateLocalStateFromApi(resultApi.data);
                updateDefectFrequencies(defectIds, quantity);
                emitInspectionUpdate({ data: resultApi.data });
                if (quickInspectionModal) quickInspectionModal.hide();
                // Reset form
                document.getElementById('quick-quantity').value = 1;
                document.getElementById('quick-pass').checked = true;
                document.getElementById('quick-defect-section').classList.add('d-none');
                checkedBoxes.forEach(cb => cb.checked = false);
                document.querySelectorAll('#quick-defect-list-modal .defect-card.selected').forEach(card => {
                    card.classList.remove('selected');
                });
            } else {
                alert('Có lỗi xảy ra khi lưu kết quả.');
            }
        }
    } catch (error) {
        console.error('Error submitting quick inspection:', error);
        alert('Có lỗi xảy ra khi lưu kết quả.');
    } finally {
        button.disabled = false;
    }
}

// Submit quick pass
async function submitQuickPass(quantity) {
    const button = document.getElementById('btn-quick-inspection-submit');
    button.disabled = true;

    try {
        const result = await apiCall(`/inspections-pass/${window.inspectionData.lineId}`, 'POST', {
            daily_plan_id: window.inspectionData.dailyPlanId,
            quantity: quantity,
        });

        if (result.success && result.data) {
            updateLocalStateFromApi(result.data);
            emitInspectionUpdate({ data: result.data });
            if (quickInspectionModal) quickInspectionModal.hide();
            // Reset form
            document.getElementById('quick-quantity').value = 1;
            document.getElementById('quick-pass').checked = true;
        } else {
            alert('Có lỗi xảy ra khi lưu kết quả.');
        }
    } catch (error) {
        console.error('Error submitting quick pass:', error);
        alert('Có lỗi xảy ra khi lưu kết quả.');
    } finally {
        button.disabled = false;
    }
}
