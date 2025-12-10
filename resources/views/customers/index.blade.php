@extends('layouts.app')

@section('title', 'Clientes - Taller Mecánico')

@section('content')
    <div class="header">
        <h1><span>👥</span> Clientes</h1>
        <div class="user-info">
            <a href="{{ route('dashboard') }}" class="back-btn">← Volver al Dashboard</a>
            <span class="user-name" id="userName"></span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="showModal()">
                ➕ Nuevo Cliente
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar clientes..." onkeyup="searchCustomers()">
            </div>
        </div>

        <div class="loader" id="loader">
            <div class="spinner"></div>
            <p>Cargando clientes...</p>
        </div>

        <div id="content" style="display: none;">
            <div class="table-container">
                <table id="customersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Documento</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="customersBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Cliente</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="customerForm">
                @csrf
                <input type="hidden" id="customerId">

                <div class="form-group">
                    <label for="name">Nombre *</label>
                    <input type="text" id="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email">
                </div>

                <div class="form-group">
                    <label for="phone">Teléfono *</label>
                    <input type="tel" id="phone" required>
                </div>

                <div class="form-group">
                    <label for="document">Documento</label>
                    <input type="text" id="document">
                </div>

                <div class="form-group">
                    <label for="address">Dirección</label>
                    <textarea id="address" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let customers = [];
        let editingId = null;

        // Check auth
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');

        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        document.getElementById('userName').textContent = user.name || 'Usuario';

        async function loadCustomers() {
            try {
                const response = await apiRequest('customers');
                customers = Array.isArray(response) ? response : (response.data || response);
                console.log('Customers:', customers);
                displayCustomers(customers);

                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } catch (error) {
                showError('Error al cargar clientes: ' + error.message);
            }
        }

        function displayCustomers(data) {
            const tbody = document.getElementById('customersBody');

            if (data.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">No hay clientes registrados</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(customer => `
            <tr>
                <td>${customer.id}</td>
                <td><strong>${customer.name}</strong></td>
                <td>${customer.email || '-'}</td>
                <td>${customer.phone}</td>
                <td>${customer.document || '-'}</td>
                <td>${customer.address || '-'}</td>
                <td>
                    <button class="btn-icon" onclick="editCustomer(${customer.id})" title="Editar">✏️</button>
                    <button class="btn-icon" onclick="deleteCustomer(${customer.id})" title="Eliminar">🗑️</button>
                </td>
            </tr>
        `).join('');
        }

        function searchCustomers() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filtered = customers.filter(c =>
                c.name.toLowerCase().includes(search) ||
                (c.email && c.email.toLowerCase().includes(search)) ||
                (c.phone && c.phone.includes(search)) ||
                (c.document && c.document.includes(search))
            );
            displayCustomers(filtered);
        }

        function showModal() {
            editingId = null;
            document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
            document.getElementById('customerForm').reset();
            document.getElementById('customerId').value = '';
            document.getElementById('modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        async function editCustomer(id) {
            try {
                const customer = await apiRequest(`customers/${id}`);
                editingId = id;

                document.getElementById('modalTitle').textContent = 'Editar Cliente';
                document.getElementById('customerId').value = customer.id;
                document.getElementById('name').value = customer.name;
                document.getElementById('email').value = customer.email || '';
                document.getElementById('phone').value = customer.phone;
                document.getElementById('document').value = customer.document || '';
                document.getElementById('address').value = customer.address || '';

                document.getElementById('modal').style.display = 'block';
            } catch (error) {
                showError('Error al cargar cliente: ' + error.message);
            }
        }

        async function deleteCustomer(id) {
            if (!confirm('¿Estás seguro de eliminar este cliente?')) return;

            try {
                await apiRequest(`customers/${id}`, 'DELETE');
                showSuccess('Cliente eliminado exitosamente');
                loadCustomers();
            } catch (error) {
                showError('Error al eliminar cliente: ' + error.message);
            }
        }

        document.getElementById('customerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value || null,
                phone: document.getElementById('phone').value,
                document: document.getElementById('document').value || null,
                address: document.getElementById('address').value || null
            };

            try {
                if (editingId) {
                    await apiRequest(`customers/${editingId}`, 'PUT', data);
                    showSuccess('Cliente actualizado exitosamente');
                } else {
                    await apiRequest('customers', 'POST', data);
                    showSuccess('Cliente creado exitosamente');
                }

                closeModal();
                loadCustomers();
            } catch (error) {
                showError('Error al guardar cliente: ' + error.message);
            }
        });

        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target == modal) {
                closeModal();
            }
        }

        loadCustomers();
    </script>
@endsection
