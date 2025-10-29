// Check authentication
if (!localStorage.getItem('token')) {
    window.location.href = 'index.html';
}

const user = JSON.parse(localStorage.getItem('user'));

// Check if user is buyer
if (user.role !== 'buyer') {
    window.location.href = 'seller-dashboard.html';
}

document.getElementById('userName').textContent = `Welcome, ${user.name}`;

// Store sellers data globally
let sellersData = [];

// Load sellers for dropdown
async function loadSellers() {
    try {
        console.log('🔄 Loading sellers...');
        console.log('Token:', localStorage.getItem('token'));
        console.log('Headers:', config.getHeaders(true));
        
        const response = await fetch(`${config.apiUrl}/sellers`, {
            headers: config.getHeaders(true)
        });
        
        console.log('Sellers response status:', response.status);

        if (response.ok) {
            sellersData = await response.json();
            console.log('Sellers loaded:', sellersData);
            const sellerSelect = document.getElementById('sellerId');

            if (sellersData.length === 0) {
                sellerSelect.innerHTML = '<option value="">No sellers available</option>';
                showToast('No sellers available. Please contact administrator.', 'warning');
                return;
            }

            sellerSelect.innerHTML = '<option value="">Select a seller</option>';
            sellersData.forEach(seller => {
                const priceText = seller.has_price ? ` (KSH ${seller.price_per_unit}/unit)` : ' (No price set)';
                sellerSelect.innerHTML += `<option value="${seller.id}" ${!seller.has_price ? 'disabled' : ''}>${seller.name}${priceText}</option>`;
            });

            console.log('Sellers dropdown populated with', sellersData.length, 'sellers');
        } else {
            console.error('Error loading sellers:', response.status);
            showToast('Error loading sellers list', 'error');
        }
    } catch (error) {
        console.error('Network error loading sellers:', error);
        showToast('Network error loading sellers', 'error');
    }
}

// Handle seller selection
document.getElementById('sellerId').addEventListener('change', function() {
    const sellerId = this.value;
    const priceDisplay = document.getElementById('priceDisplay');
    
    if (sellerId) {
        const selectedSeller = sellersData.find(seller => seller.id == sellerId);
        if (selectedSeller && selectedSeller.has_price) {
            priceDisplay.value = `KSH ${selectedSeller.price_per_unit}`;
            // Trigger total calculation
            calculateTotal();
        } else {
            priceDisplay.value = 'No price set';
        }
    } else {
        priceDisplay.value = 'Select seller first';
    }
});

// Auto-calculate total amount
document.getElementById('quantity').addEventListener('input', calculateTotal);

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const sellerId = document.getElementById('sellerId').value;
    
    if (sellerId) {
        const selectedSeller = sellersData.find(seller => seller.id == sellerId);
        if (selectedSeller && selectedSeller.has_price) {
            const price = parseFloat(selectedSeller.price_per_unit);
            const total = (quantity * price).toFixed(2);
            document.getElementById('totalAmount').value = `KSH ${total}`;
            return;
        }
    }
    
    document.getElementById('totalAmount').value = 'KSH 0.00';
}

// Load stats
async function loadStats() {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/stats`, {
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            const data = await response.json();
            document.getElementById('totalOrders').textContent = data.total_orders;
            document.getElementById('paidOrders').textContent = data.paid_orders;
            document.getElementById('pendingAmount').textContent = `KSH ${parseFloat(data.pending_amount || 0).toFixed(2)}`;
            document.getElementById('totalSpent').textContent = `KSH ${parseFloat(data.total_spent || 0).toFixed(2)}`;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load mandazi records
async function loadMandazi() {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi`, {
            headers: config.getHeaders(true)
        });
        
        if (response.status === 401) {
            localStorage.clear();
            window.location.href = 'index.html';
            return;
        }
        
        const data = await response.json();
        displayMandazi(data);
    } catch (error) {
        showToast('Error loading orders', 'error');
    }
}

function displayMandazi(records) {
    const tbody = document.getElementById('mandaziTableBody');
    
    if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No orders found. Place your first order above!</td></tr>';
        return;
    }
    
    tbody.innerHTML = records.map(record => {
        const statusBadge = getStatusBadge(record.status);
        const payButton = record.status === 'Pending' 
            ? `<button class="btn btn-payment btn-sm" onclick="initiatePayment(event, ${record.id}, '${record.phone_number}')">
                <i class="fas fa-mobile-alt me-1"></i>Pay Now
               </button>` 
            : '';
        
        return `
            <tr>
                <td><strong>#${record.id}</strong></td>
                <td>${record.seller?.name || 'Unknown Seller'}</td>
                <td>${record.quantity}</td>
                <td>KSH ${parseFloat(record.price_per_unit).toFixed(2)}</td>
                <td><strong>KSH ${parseFloat(record.total_amount).toFixed(2)}</strong></td>
                <td>${record.phone_number}</td>
                <td>${statusBadge}</td>
                <td>${new Date(record.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                })}</td>
                <td>
                    ${payButton}
                    <button class="btn btn-info btn-sm" onclick="checkStatus(${record.id})">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    ${record.status === 'Pending' ? `<button class="btn btn-danger btn-sm" onclick="deleteRecord(${record.id})"><i class="fas fa-trash"></i></button>` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusBadge(status) {
    const badges = {
        'Pending': '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>',
        'Paid': '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>',
        'Failed': '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Failed</span>'
    };
    return badges[status] || status;
}

// Add new mandazi record
document.getElementById('mandaziForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const sellerId = document.getElementById('sellerId').value;

    if (!sellerId) {
        showToast('Please select a seller from the dropdown', 'error');
        return;
    }

    const quantity = document.getElementById('quantity').value;
    const phoneNumber = document.getElementById('phoneNumber').value;
    
    if (!quantity || quantity < 1) {
        showToast('Please enter a valid quantity', 'error');
        return;
    }
    
    // Check if selected seller has a price
    const selectedSeller = sellersData.find(seller => seller.id == sellerId);
    if (!selectedSeller || !selectedSeller.has_price) {
        showToast('Selected seller has not set a price yet', 'error');
        return;
    }
    
    if (!phoneNumber || !phoneNumber.match(/^254[0-9]{9}$/)) {
        showToast('Please enter a valid M-Pesa number (format: 254XXXXXXXXX)', 'error');
        return;
    }

    const formData = {
        quantity: parseInt(quantity),
        phone_number: phoneNumber,
        seller_id: parseInt(sellerId)
    };

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Placing Order...';

    try {
        const response = await fetch(`${config.apiUrl}/mandazi`, {
            method: 'POST',
            headers: config.getHeaders(true),
            body: JSON.stringify(formData)
        });
        
        if (response.ok) {
            showToast('Order placed successfully!', 'success');
            document.getElementById('mandaziForm').reset();
            document.getElementById('totalAmount').value = '';
            loadMandazi();
            loadStats();
        } else {
            const errorData = await response.json();
            const errorMessage = errorData.message || 'Error placing order';
            showToast(errorMessage, 'error');
        }
    } catch (error) {
        showToast('Network error: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Place Order';
    }
});

// Logout
document.getElementById('logoutBtn').addEventListener('click', () => {
    localStorage.clear();
    window.location.href = 'index.html';
});

// Toast notification
function showToast(message, type) {
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle', warning: 'exclamation-triangle' };
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast toast-${type} show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="toast-body d-flex align-items-center">
            <i class="fas fa-${icons[type]} me-3 fs-4"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close btn-close-white ms-2" onclick="document.getElementById('${toastId}').remove()"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Initiate payment with confirmation
async function initiatePayment(event, mandaziId, phoneNumber) {
    event.preventDefault();
    
    // Show confirmation dialog
    const confirmed = confirm(`🔔 Send STK Push?\n\nThis will send a payment request to:\n📱 ${phoneNumber}\n\nPress OK to proceed or Cancel to abort.`);
    
    if (!confirmed) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending STK...';
    
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/${mandaziId}/pay`, {
            method: 'POST',
            headers: config.getHeaders(true),
            body: JSON.stringify({
                phone_number: phoneNumber
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Show different messages based on mode
            if (data.mode === 'real_stk_push') {
                showToast('📱 Real STK Push sent! Check your phone and enter M-Pesa PIN.', 'success');
            } else if (data.mode === 'simulation') {
                showToast('🎭 Demo Mode: Payment simulated successfully!', 'info');
            } else {
                showToast('📱 STK Push sent! Check your phone and enter M-Pesa PIN.', 'success');
            }
            
            // Start checking payment status
            checkPaymentStatus(mandaziId, button, originalText);
        } else {
            showToast(`❌ Payment failed: ${data.message || 'Unknown error'}`, 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    } catch (error) {
        showToast(`❌ Network error: ${error.message}`, 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// Check payment status periodically
async function checkPaymentStatus(mandaziId, button, originalText, attempts = 0) {
    const maxAttempts = 30; // Check for 30 seconds
    
    if (attempts >= maxAttempts) {
        button.disabled = false;
        button.innerHTML = originalText;
        showToast('⏰ Payment timeout. Please check manually or try again.', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/${mandaziId}/status`, {
            headers: config.getHeaders(true)
        });
        
        const data = await response.json();
        
        if (data.success && data.status === 'Paid') {
            button.innerHTML = '<i class="fas fa-check-circle me-1"></i>Paid';
            button.className = 'btn btn-success btn-sm';
            button.disabled = true;
            showToast('✅ Payment successful! Order status updated.', 'success');
            
            // Refresh the orders table
            setTimeout(() => {
                loadMandazi();
                loadStats();
            }, 1000);
            return;
        }
        
        // Continue checking
        setTimeout(() => {
            checkPaymentStatus(mandaziId, button, originalText, attempts + 1);
        }, 2000); // Check every 2 seconds
        
    } catch (error) {
        console.error('Status check error:', error);
        setTimeout(() => {
            checkPaymentStatus(mandaziId, button, originalText, attempts + 1);
        }, 2000);
    }
}

// Check status function for manual refresh
async function checkStatus(mandaziId) {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/${mandaziId}/status`, {
            headers: config.getHeaders(true)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`📋 Order status: ${data.status}`, 'info');
            loadMandazi(); // Refresh the table
        } else {
            showToast('❌ Failed to check status', 'error');
        }
    } catch (error) {
        showToast('❌ Network error checking status', 'error');
    }
}

// Load on page load
document.addEventListener('DOMContentLoaded', async function() {
    // Check connection first
    const isConnected = await config.showConnectionStatus();
    
    if (isConnected) {
        loadStats();
        loadMandazi();
        loadSellers();
    } else {
        showToast('⚠️ Backend server not running. Please start Laravel server on port 8001.', 'warning');
    }
});
