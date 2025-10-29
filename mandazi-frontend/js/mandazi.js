// Check authentication
if (!localStorage.getItem('token')) {
    window.location.href = 'index.html';
}

const user = JSON.parse(localStorage.getItem('user'));
document.getElementById('userName').textContent = `Welcome, ${user.name}`;

// Auto-calculate total amount
document.getElementById('quantity').addEventListener('input', calculateTotal);
document.getElementById('price').addEventListener('input', calculateTotal);

function calculateTotal() {
    const quantity = document.getElementById('quantity').value || 0;
    const price = document.getElementById('price').value || 0;
    const total = (quantity * price).toFixed(2);
    document.getElementById('totalAmount').value = `KSH ${total}`;
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
        showAlert('Error loading records', 'danger');
    }
}

function displayMandazi(records) {
    const tbody = document.getElementById('mandaziTableBody');
    
    if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No records found</td></tr>';
        return;
    }
    
    tbody.innerHTML = records.map(record => {
        const statusBadge = getStatusBadge(record.status);
        const payButton = record.status === 'Pending' 
            ? `<button class="btn btn-success btn-sm" onclick="initiatePayment(${record.id}, '${record.phone_number}')">Pay Now</button>`
            : '';
        
        return `
            <tr>
                <td>${record.id}</td>
                <td>${record.quantity}</td>
                <td>KSH ${parseFloat(record.price_per_unit).toFixed(2)}</td>
                <td>KSH ${parseFloat(record.total_amount).toFixed(2)}</td>
                <td>${record.phone_number}</td>
                <td>${statusBadge}</td>
                <td>${new Date(record.created_at).toLocaleDateString()}</td>
                <td>
                    ${payButton}
                    <button class="btn btn-info btn-sm" onclick="checkStatus(${record.id})">Check</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteRecord(${record.id})">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusBadge(status) {
    const badges = {
        'Pending': '<span class="badge bg-warning text-dark">Pending</span>',
        'Paid': '<span class="badge bg-success">Paid</span>',
        'Failed': '<span class="badge bg-danger">Failed</span>'
    };
    return badges[status] || status;
}

// Add new mandazi record
document.getElementById('mandaziForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';
    
    const formData = {
        quantity: parseInt(document.getElementById('quantity').value),
        price_per_unit: parseFloat(document.getElementById('price').value),
        phone_number: document.getElementById('phoneNumber').value
    };
    
    try {
        const response = await fetch(`${config.apiUrl}/mandazi`, {
            method: 'POST',
            headers: config.getHeaders(true),
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showAlert('Record added successfully!', 'success');
            document.getElementById('mandaziForm').reset();
            document.getElementById('totalAmount').value = '';
            loadMandazi();
        } else {
            showAlert(data.message || 'Error adding record', 'danger');
        }
    } catch (error) {
        showAlert('Network error', 'danger');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Record';
    }
});

// Initiate M-Pesa payment
async function initiatePayment(mandaziId, phoneNumber) {
    if (!confirm('Initiate M-Pesa payment for this record?')) return;
    
    showAlert('Sending payment request to M-Pesa...', 'info');
    
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/${mandaziId}/pay`, {
            method: 'POST',
            headers: config.getHeaders(true),
            body: JSON.stringify({ phone_number: phoneNumber })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showAlert('Payment request sent! Please check your phone for M-Pesa prompt.', 'success');
            
            setTimeout(() => {
                checkStatus(mandaziId);
            }, 10000);
        } else {
            showAlert(data.message || 'Payment initiation failed', 'danger');
        }
    } catch (error) {
        showAlert('Network error during payment', 'danger');
    }
}

// Check payment status
async function checkStatus(mandaziId) {
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/${mandaziId}/status`, {
            headers: config.getHeaders(true)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showAlert(`Payment Status: ${data.status}`, 'info');
            loadMandazi();
        } else {
            showAlert('Error checking status', 'danger');
        }
    } catch (error) {
        showAlert('Network error', 'danger');
    }
}

// Delete record
async function deleteRecord(mandaziId) {
    if (!confirm('Are you sure you want to delete this record?')) return;
    
    try {
        const response = await fetch(`${config.apiUrl}/mandazi/${mandaziId}`, {
            method: 'DELETE',
            headers: config.getHeaders(true)
        });
        
        if (response.ok) {
            showAlert('Record deleted successfully', 'success');
            loadMandazi();
        } else {
            showAlert('Error deleting record', 'danger');
        }
    } catch (error) {
        showAlert('Network error', 'danger');
    }
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

// Refresh button
document.getElementById('refreshBtn').addEventListener('click', loadMandazi);

// Show alert function
function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => alertContainer.innerHTML = '', 150);
        }
    }, 5000);
}

// Load records on page load
loadMandazi();
``