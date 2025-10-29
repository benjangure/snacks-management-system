// API Configuration - Auto-detect environment
const isFileProtocol = window.location.protocol === 'file:';
const apiUrl = 'http://127.0.0.1:8001/api';

console.log('🔧 Mandazi Config Loaded');
console.log('📍 Protocol:', window.location.protocol);
console.log('🌐 API URL:', apiUrl);

if (isFileProtocol) {
    console.log('📁 Running from file system - Perfect for direct HTML opening!');
}

// Cache detection
const configVersion = '2025-10-28-v3';
const lastVersion = localStorage.getItem('mandazi_config_version');
if (lastVersion !== configVersion) {
    console.log('🔄 Config updated to version', configVersion);
    localStorage.setItem('mandazi_config_version', configVersion);
}

const config = {
    apiUrl: apiUrl,

    getHeaders: (includeAuth = true) => {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
        
        if (includeAuth) {
            const token = localStorage.getItem('token');
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }
        }
        
        return headers;
    },

    // Check if backend is running
    async checkConnection() {
        try {
            const response = await fetch(`${this.apiUrl}/test-simple`, {
                method: 'GET',
                headers: this.getHeaders(false)
            });
            return response.ok;
        } catch (error) {
            return false;
        }
    },

    // Show connection status with retry
    async showConnectionStatus(retryCount = 0) {
        const maxRetries = 3;
        const isConnected = await this.checkConnection();
        const statusElement = document.getElementById('connection-status');
        
        if (statusElement) {
            if (isConnected) {
                statusElement.innerHTML = '<i class="fas fa-check-circle me-1"></i>Connected';
                statusElement.className = 'badge bg-success';
            } else if (retryCount < maxRetries) {
                statusElement.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>Connecting... (${retryCount + 1}/${maxRetries})`;
                statusElement.className = 'badge bg-info';
                
                // Retry after 2 seconds
                setTimeout(() => {
                    this.showConnectionStatus(retryCount + 1);
                }, 2000);
            } else {
                statusElement.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Offline';
                statusElement.className = 'badge bg-warning';
                
                // Show helpful message
                if (typeof showToast === 'function') {
                    showToast('💡 Start the backend: Run start-mandazi-system.bat or php artisan serve --port=8001', 'info');
                }
            }
        }
        
        return isConnected;
    }
};
