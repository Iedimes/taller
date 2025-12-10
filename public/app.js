// API Base URL
const API_URL = 'http://localhost:8000/api';

// Check authentication
function checkAuth() {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    if (!token) {
        window.location.href = 'test-login.html';
        return false;
    }

    // Display user name in header
    const userNameEl = document.getElementById('userName');
    if (userNameEl) {
        userNameEl.textContent = user.name || 'Usuario';
    }

    return true;
}

// API Request helper
async function apiRequest(endpoint, method = 'GET', data = null) {
    const token = localStorage.getItem('auth_token');

    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`${API_URL}/${endpoint}`, options);

        // Check if token is invalid
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = 'test-login.html';
            throw new Error('Sesión expirada');
        }

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Error en la petición');
        }

        // For DELETE requests, return success
        if (method === 'DELETE') {
            return { success: true };
        }

        return await response.json();

    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Logout function
function logout() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        // Call logout endpoint
        apiRequest('logout', 'POST').catch(() => {});

        // Clear local storage
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');

        // Redirect to login
        window.location.href = 'test-login.html';
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.notification');
    existing.forEach(el => el.remove());

    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Shorthand notification functions
function showSuccess(message) {
    showNotification(message, 'success');
}

function showError(message) {
    showNotification(message, 'error');
}

function showInfo(message) {
    showNotification(message, 'info');
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).format(date);
}

// Format datetime
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

// Get status badge HTML
function getStatusBadge(status) {
    const statusMap = {
        'pending': { class: 'warning', text: 'Pendiente' },
        'in_progress': { class: 'info', text: 'En Progreso' },
        'completed': { class: 'success', text: 'Completado' },
        'delivered': { class: 'success', text: 'Entregado' },
        'cancelled': { class: 'danger', text: 'Cancelado' }
    };

    const badge = statusMap[status] || { class: 'info', text: status };
    return `<span class="badge badge-${badge.class}">${badge.text}</span>`;
}

// Confirm dialog
function confirmAction(message) {
    return confirm(message);
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add slideOutRight animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
