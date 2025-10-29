// Check authentication
if (!localStorage.getItem('token')) {
    window.location.href = 'index.html';
}

const user = JSON.parse(localStorage.getItem('user'));

// Check if user is seller
if (user.role !== 'seller') {
    window.location.href = 'buyer-dashboard.html';
}

document.getElementById('userName').textContent = `${user.name}`;

// Load current seller price
async function loadSellerPrice() {
    try {
        const response = await fetch(`${config.apiUrl}/seller/price`, {
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.has_price) {
                document.getElementById('currentPrice').textContent = `KSH ${data.price}`;
                document.getElementById('priceInput').value = data.price;
            } else {
                document.getElementById('currentPrice').textContent = 'Not set';
            }
        }
    } catch (error) {
        console.error('Error loading seller price:', error);
    }
}

// Load dashboard stats
async function loadDashboard() {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/stats`, {
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            const data = await response.json();
            document.getElementById('totalSales').textContent = `KSH ${parseFloat(data.total_sales || 0).toFixed(2)}`;
            document.getElementById('pendingAmount').textContent = `KSH ${parseFloat(data.pending_amount || 0).toFixed(2)}`;
            document.getElementById('totalOrders').textContent = data.total_orders || 0;
            document.getElementById('totalCustomers').textContent = data.unique_customers || 0;
            document.getElementById('paidOrders').textContent = data.paid_orders || 0;
            
            // Calculate pending and failed orders
            const pendingOrders = (data.total_orders || 0) - (data.paid_orders || 0);
            document.getElementById('pendingOrders').textContent = pendingOrders;
            document.getElementById('failedOrders').textContent = 0; // This would need to be added to backend
        }
    } catch (error) {
        showToast('Error loading dashboard', 'error');
    }
}

// Load all orders for this seller
async function loadOrders() {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi`, {
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            const data = await response.json();
            displayOrders(data);
        }
    } catch (error) {
        showToast('Error loading orders', 'error');
    }
}

function displayOrders(orders) {
    const tbody = document.getElementById('ordersTableBody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No orders yet</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => {
        const statusBadge = getStatusBadge(order.status);
        
        return `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${order.user.name}</td>
                <td>${order.quantity}</td>
                <td>KSH ${parseFloat(order.price_per_unit).toFixed(2)}</td>
                <td><strong>KSH ${parseFloat(order.total_amount).toFixed(2)}</strong></td>
                <td>${order.phone_number}</td>
                <td>${statusBadge}</td>
                <td>${new Date(order.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</td>
            </tr>
        `;
    }).join('');
}

// Load customers who have ordered from this seller
async function loadCustomers() {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi`, {
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            const orders = await response.json();
            
            // Group orders by customer
            const customerMap = new Map();
            orders.forEach(order => {
                const customerId = order.user.id;
                if (!customerMap.has(customerId)) {
                    customerMap.set(customerId, {
                        id: customerId,
                        name: order.user.name,
                        email: order.user.email,
                        mandazi_count: 0,
                        mandazi_sum_total_amount: 0
                    });
                }
                
                const customer = customerMap.get(customerId);
                customer.mandazi_count++;
                if (order.status === 'Paid') {
                    customer.mandazi_sum_total_amount += parseFloat(order.total_amount);
                }
            });
            
            const customers = Array.from(customerMap.values());
            displayCustomers(customers);
        }
    } catch (error) {
        showToast('Error loading customers', 'error');
    }
}

function displayCustomers(customers) {
    const tbody = document.getElementById('customersTableBody');
    
    if (customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No customers yet</td></tr>';
        return;
    }
    
    tbody.innerHTML = customers.map(customer => {
        return `
            <tr>
                <td><strong>#${customer.id}</strong></td>
                <td><i class="fas fa-user-circle me-2 text-primary"></i>${customer.name}</td>
                <td>${customer.email}</td>
                <td><span class="badge bg-info">${customer.mandazi_count || 0} orders</span></td>
                <td><strong>KSH ${parseFloat(customer.mandazi_sum_total_amount || 0).toFixed(2)}</strong></td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="viewCustomerOrders(${customer.id}, '${customer.name}')">
                        <i class="fas fa-eye me-1"></i>View Orders
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// View customer orders
async function viewCustomerOrders(userId, userName) {
    document.getElementById('modalCustomerName').textContent = `${userName}'s Orders`;
    
    try {
        const response = await fetch(`${config.apiUrl}/mandazi`, {
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            const allOrders = await response.json();
            const customerOrders = allOrders.filter(order => order.user.id === userId);
            displayCustomerOrders(customerOrders);
            
            const modal = new bootstrap.Modal(document.getElementById('customerOrdersModal'));
            modal.show();
        }
    } catch (error) {
        showToast('Error loading customer orders', 'error');
    }
}

function displayCustomerOrders(orders) {
    const tbody = document.getElementById('customerOrdersBody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No orders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => {
        const statusBadge = getStatusBadge(order.status);
        
        return `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${order.quantity}</td>
                <td>KSH ${parseFloat(order.price_per_unit).toFixed(2)}</td>
                <td><strong>KSH ${parseFloat(order.total_amount).toFixed(2)}</strong></td>
                <td>${order.phone_number}</td>
                <td>${statusBadge}</td>
                <td>${new Date(order.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric'
                })}</td>
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

// Logout
document.getElementById('logoutBtn').addEventListener('click', async () => {
    try {
        await fetch(`${config.apiUrl}/logout`, {
            method: 'POST',
            headers: config.getHeaders(true)
        });
    } catch (error) {
        console.error('Logout error:', error);
    }
    
    localStorage.clear();
    window.location.href = 'index.html';
});

// Refresh buttons
document.getElementById('refreshOrdersBtn').addEventListener('click', () => {
    loadOrders();
    loadDashboard();
    showToast('Orders refreshed!', 'success');
});

document.getElementById('refreshCustomersBtn').addEventListener('click', () => {
    loadCustomers();
    showToast('Customers refreshed!', 'success');
});

// Toast notification function
function showToast(message, type) {
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        info: 'info-circle',
        warning: 'exclamation-triangle'
    };
    
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

// Update price functionality
document.getElementById('updatePriceBtn').addEventListener('click', async () => {
    const priceInput = document.getElementById('priceInput');
    const price = parseFloat(priceInput.value);
    const updateBtn = document.getElementById('updatePriceBtn');
    
    if (!price || price <= 0) {
        showToast('Please enter a valid price', 'error');
        return;
    }
    
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    
    try {
        const response = await fetch(`${config.apiUrl}/seller/price`, {
            method: 'POST',
            headers: config.getHeaders(true),
            body: JSON.stringify({ price_per_unit: price })
        });
        
        if (response.ok) {
            const data = await response.json();
            document.getElementById('currentPrice').textContent = `KSH ${data.price}`;
            showToast('Price updated successfully!', 'success');
        } else {
            const errorData = await response.json();
            showToast(errorData.message || 'Error updating price', 'error');
        }
    } catch (error) {
        showToast('Network error: ' + error.message, 'error');
    } finally {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Price';
    }
});

// Load data on page load
document.addEventListener('DOMContentLoaded', async function() {
    // Check connection first
    const isConnected = await config.showConnectionStatus();
    
    if (isConnected) {
        loadDashboard();
        loadOrders();
        loadCustomers();
        loadSellerPrice();
    } else {
        showToast('⚠️ Backend server not running. Please start Laravel server on port 8001.', 'warning');
    }
});

// Auto-refresh every 30 seconds
setInterval(() => {
    loadDashboard();
    loadOrders();
    loadCustomers();
    loadSellerPrice();
}, 30000);