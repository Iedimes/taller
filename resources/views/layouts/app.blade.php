<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Taller Mecánico')</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn,
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .back-btn:hover,
        .logout-btn:hover {
            background: white;
            color: #667eea;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-icon {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 5px 10px;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-top: 1px solid #f0f0f0;
            font-size: 14px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }

        .modal-content form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        /* Actions Bar */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            width: 300px;
        }

        /* Loader */
        .loader {
            text-align: center;
            padding: 60px 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            max-width: 400px;
        }

        .notification.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .notification.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
    </style>
    @yield('styles')
</head>

<body>
    @yield('content')

    <script>
        // API Base URL
        const API_URL = 'http://localhost:8000/api';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // API Request helper
        async function apiRequest(endpoint, method = 'GET', data = null) {
            const token = localStorage.getItem('auth_token');

            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            };

            // Add authorization if token exists
            if (token) {
                options.headers['Authorization'] = `Bearer ${token}`;
            }

            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(`${API_URL}/${endpoint}`, options);

                if (response.status === 401) {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                    throw new Error('Sesión expirada');
                }

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Error en la petición');
                }

                if (method === 'DELETE') {
                    return {
                        success: true
                    };
                }

                return await response.json();

            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const existing = document.querySelectorAll('.notification');
            existing.forEach(el => el.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => notification.remove(), 3000);
        }

        function showSuccess(message) {
            showNotification(message, 'success');
        }

        function showError(message) {
            showNotification(message, 'error');
        }

        // Global Currency Formatter (Guaraníes - PYG)
        function formatMoney(amount) {
            return new Intl.NumberFormat('es-PY', {
                style: 'currency',
                currency: 'PYG',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount).replace('PYG', 'Gs.');
        }

        function logout() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                apiRequest('logout', 'POST').catch(() => {});
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        }
    </script>

    @yield('scripts')
</body>

</html>
