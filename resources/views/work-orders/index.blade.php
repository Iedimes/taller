@extends('layouts.app')

@section('title', 'Órdenes de Trabajo - Taller Mecánico')

@section('content')
    <div class="header">
        <h1><span>📋</span> Órdenes de Trabajo</h1>
        <div class="user-info">
            <a href="{{ route('dashboard') }}" class="back-btn">← Volver al Dashboard</a>
            <span class="user-name" id="userName"></span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="showModal()">
                ➕ Nueva Orden
            </button>
            <div class="search-box">
                <select id="statusFilter" onchange="loadOrders()"
                    style="padding: 10px; border-radius: 8px; border: 2px solid #ddd; margin-right: 10px;">
                    <option value="">Todos los Estados</option>
                    <option value="pending">Pendientes</option>
                    <option value="in_progress">En Progreso</option>
                    <option value="completed">Completadas</option>
                    <option value="delivered">Entregadas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
        </div>

        <div class="loader" id="loader">
            <div class="spinner"></div>
            <p>Cargando órdenes...</p>
        </div>

        <div id="content" style="display: none;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Orden #</th>
                            <th>Cliente / Vehículo</th>
                            <th>Fecha Ingreso</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ordersBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nueva Orden</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="orderForm">
                @csrf

                <div class="form-group">
                    <label for="customer_id">1. Seleccionar Cliente *</label>
                    <select id="customer_id" required onchange="loadCustomerVehicles(this.value)">
                        <option value="">Seleccione...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="vehicle_id">2. Seleccionar Vehículo *</label>
                    <select id="vehicle_id" required disabled>
                        <option value="">Primero seleccione cliente...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="entry_date">Fecha de Ingreso *</label>
                    <input type="datetime-local" id="entry_date" required>
                </div>

                <div class="form-group">
                    <label for="description">Descripción Inicial / Diagnóstico</label>
                    <textarea id="description" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Orden</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>Actualizar Estado</h2>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <form id="statusForm">
                <input type="hidden" id="statusOrderId">
                <div class="form-group">
                    <label for="new_status">Nuevo Estado:</label>
                    <select id="new_status" required style="width: 100%; padding: 10px; border-radius: 8px;">
                        <option value="pending">Pendiente 🟡</option>
                        <option value="in_progress">En Progreso 🔵</option>
                        <option value="completed">Completado 🟢</option>
                        <option value="delivered">Entregado ✅</option>
                        <option value="cancelled">Cancelado ❌</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let orders = [];
        let customers = [];

        // Auth
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!token) window.location.href = '{{ route('login') }}';
        document.getElementById('userName').textContent = user.name || 'Usuario';

        async function loadOrders() {
            const status = document.getElementById('statusFilter').value;
            const endpoint = status ? `work-orders-status/${status}` : 'work-orders';

            try {
                document.getElementById('loader').style.display = 'block';
                document.getElementById('content').style.display = 'none';

                const response = await apiRequest(endpoint);
                orders = Array.isArray(response) ? response : (response.data || response);
                displayOrders(orders);

                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } catch (error) {
                showError('Error al cargar órdenes: ' + error.message);
            }
        }

        function formatMoney(amount) {
            if (amount === undefined || amount === null) return 'S/ 0.00';
            return new Intl.NumberFormat('es-PE', {
                style: 'currency',
                currency: 'PEN'
            }).format(amount);
        }

        function formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('es-PE', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getStatusBadge(status) {
            const map = {
                'pending': {
                    color: '#f1c40f',
                    text: 'Pendiente'
                },
                'in_progress': {
                    color: '#3498db',
                    text: 'En Progreso'
                },
                'completed': {
                    color: '#2ecc71',
                    text: 'Completado'
                },
                'delivered': {
                    color: '#27ae60',
                    text: 'Entregado'
                },
                'cancelled': {
                    color: '#e74c3c',
                    text: 'Cancelado'
                }
            };
            const s = map[status] || {
                color: '#95a5a6',
                text: status
            };
            return `<span style="background:${s.color}20; color:${s.color}; padding: 4px 10px; border-radius: 20px; font-weight:bold; font-size:12px">${s.text}</span>`;
        }

        function displayOrders(data) {
            const tbody = document.getElementById('ordersBody');

            if (!data || data.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">No hay órdenes registradas</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(order => `
            <tr>
                <td><a href="/work-orders/${order.id}" style="font-weight:bold; color:#2980b9; text-decoration:none">#${order.order_number}</a></td>
                <td>
                    ${order.vehicle ? `
                                    <div>🚗 <strong>${order.vehicle.plate}</strong></div>
                                    <div style="font-size:12px; color:#666">
                                        ${(order.vehicle.customer || {}).name || ((order.vehicle.customer_data || {}).name || '-')}
                                    </div>
                                ` : 'Vehículo no asignado'}
                </td>
                <td>${formatDate(order.entry_date)}</td>
                <td>
                    <button onclick="openStatusModal(${order.id}, '${order.status}')" style="background:none; border:none; cursor:pointer" title="Cambiar Estado">
                        ${getStatusBadge(order.status)}
                    </button>
                </td>
                <td style="font-weight:bold; color:#2c3e50">${formatMoney(order.total_price)}</td>
                <td>
                    <a href="/work-orders/${order.id}" class="btn-icon" style="text-decoration:none" title="Ver Detalles / Editar">✏️</a>
                    <a href="/work-orders/${order.id}/invoice" target="_blank" class="btn-icon" style="text-decoration:none" title="Imprimir Factura">🖨️</a>
                    <button class="btn-icon" onclick="deleteOrder(${order.id})" title="Eliminar">🗑️</button>
                </td>
            </tr>
        `).join('');
        }

        // --- Status Modal Logic ---
        function openStatusModal(id, currentStatus) {
            document.getElementById('statusOrderId').value = id;
            document.getElementById('new_status').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        document.getElementById('statusForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('statusOrderId').value;
            const status = document.getElementById('new_status').value;

            try {
                await apiRequest(`work-orders/${id}`, 'PUT', {
                    status
                });
                showSuccess('Estado actualizado exitosamente');
                closeStatusModal();
                loadOrders();
            } catch (e) {
                showError('Error al actualizar estado: ' + e.message);
            }
        });

        // --- Create Modal Logic ---
        async function showModal() {
            document.getElementById('orderForm').reset();
            try {
                const response = await apiRequest('customers');
                customers = Array.isArray(response) ? response : (response.data || response);

                const select = document.getElementById('customer_id');
                select.innerHTML = '<option value="">Seleccione...</option>' +
                    customers.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                document.getElementById('entry_date').value = now.toISOString().slice(0, 16);

                document.getElementById('modal').style.display = 'block';
            } catch (error) {
                showError('Error al cargar clientes');
            }
        }

        async function loadCustomerVehicles(customerId) {
            const vehicleSelect = document.getElementById('vehicle_id');
            if (!customerId) {
                vehicleSelect.disabled = true;
                vehicleSelect.innerHTML = '<option value="">Primero seleccione cliente...</option>';
                return;
            }

            try {
                const response = await apiRequest('vehicles');
                const allVehicles = Array.isArray(response) ? response : (response.data || response);
                const customerVehicles = allVehicles.filter(v => v.customer_id == customerId);

                if (customerVehicles.length === 0) {
                    vehicleSelect.innerHTML = '<option value="">Este cliente no tiene vehículos</option>';
                    vehicleSelect.disabled = true;
                } else {
                    vehicleSelect.innerHTML = '<option value="">Seleccione vehículo...</option>' +
                        customerVehicles.map(v => `<option value="${v.id}">${v.brand} ${v.model} (${v.plate})</option>`)
                        .join('');
                    vehicleSelect.disabled = false;
                }
            } catch (e) {
                console.error(e);
                vehicleSelect.innerHTML = '<option value="">Error al cargar vehículos</option>';
            }
        }

        async function deleteOrder(id) {
            if (!confirm('¿Estás seguro? Esto eliminará la orden y sus detalles.')) return;
            try {
                await apiRequest(`work-orders/${id}`, 'DELETE');
                showSuccess('Orden eliminada');
                loadOrders();
            } catch (e) {
                showError('Error: ' + e.message);
            }
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        document.getElementById('orderForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                vehicle_id: document.getElementById('vehicle_id').value,
                entry_date: document.getElementById('entry_date').value,
                description: document.getElementById('description').value
            };

            try {
                await apiRequest('work-orders', 'POST', data);
                showSuccess('Orden creada exitosamente');
                closeModal();
                loadOrders();
            } catch (e) {
                showError('Error al crear orden: ' + e.message);
            }
        });

        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) closeModal();
            if (event.target == document.getElementById('statusModal')) closeStatusModal();
        }

        loadOrders();
    </script>
@endsection
