@extends('layouts.app')

@section('title', 'Vehículos - Taller Mecánico')

@section('content')
    <div class="header">
        <h1><span>🚗</span> Vehículos</h1>
        <div class="user-info">
            <a href="{{ route('dashboard') }}" class="back-btn">← Volver al Dashboard</a>
            <span class="user-name" id="userName"></span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="showModal()">
                ➕ Nuevo Vehículo
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar vehículos..." onkeyup="searchVehicles()">
            </div>
        </div>

        <div class="loader" id="loader">
            <div class="spinner"></div>
            <p>Cargando vehículos...</p>
        </div>

        <div id="content" style="display: none;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Cliente</th>
                            <th>Color</th>
                            <th>Kilometraje</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="vehiclesBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Vehículo</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="vehicleForm">
                @csrf
                <input type="hidden" id="vehicleId">

                <div class="form-group">
                    <label for="customer_id">Cliente *</label>
                    <select id="customer_id" required>
                        <option value="">Seleccione un cliente</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="plate">Placa *</label>
                    <input type="text" id="plate" required>
                </div>

                <div class="form-group">
                    <label for="brand">Marca *</label>
                    <input type="text" id="brand" required>
                </div>

                <div class="form-group">
                    <label for="model">Modelo *</label>
                    <input type="text" id="model" required>
                </div>

                <div class="form-group">
                    <label for="year">Año</label>
                    <input type="text" id="year">
                </div>

                <div class="form-group">
                    <label for="vin">VIN</label>
                    <input type="text" id="vin">
                </div>

                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color">
                </div>

                <div class="form-group">
                    <label for="mileage">Kilometraje</label>
                    <input type="number" id="mileage">
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
        let vehicles = [];
        let customers = [];
        let editingId = null;

        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');

        if (!token) window.location.href = '{{ route('login') }}';
        document.getElementById('userName').textContent = user.name || 'Usuario';

        async function loadVehicles() {
            try {
                const response = await apiRequest('vehicles');
                vehicles = Array.isArray(response) ? response : (response.data || response);
                displayVehicles(vehicles);

                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } catch (error) {
                showError('Error al cargar vehículos: ' + error.message);
            }
        }

        async function loadCustomers() {
            try {
                const response = await apiRequest('customers');
                customers = Array.isArray(response) ? response : (response.data || response);

                const select = document.getElementById('customer_id');
                select.innerHTML = '<option value="">Seleccione un cliente</option>' +
                    customers.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            } catch (error) {
                console.error('Error loading customers:', error);
            }
        }

        function displayVehicles(data) {
            const tbody = document.getElementById('vehiclesBody');

            if (data.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="9" style="text-align: center; padding: 40px; color: #999;">No hay vehículos registrados</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(vehicle => `
            <tr>
                <td>${vehicle.id}</td>
                <td><strong>${vehicle.plate}</strong></td>
                <td>${vehicle.brand}</td>
                <td>${vehicle.model}</td>
                <td>${vehicle.year || '-'}</td>
                <td>${vehicle.customer ? vehicle.customer.name : '-'}</td>
                <td>${vehicle.color || '-'}</td>
                <td>${vehicle.mileage || '-'}</td>
                <td>
                    <button class="btn-icon" onclick="editVehicle(${vehicle.id})" title="Editar">✏️</button>
                    <button class="btn-icon" onclick="deleteVehicle(${vehicle.id})" title="Eliminar">🗑️</button>
                </td>
            </tr>
        `).join('');
        }

        function searchVehicles() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filtered = vehicles.filter(v =>
                v.plate.toLowerCase().includes(search) ||
                v.brand.toLowerCase().includes(search) ||
                v.model.toLowerCase().includes(search) ||
                (v.customer && v.customer.name.toLowerCase().includes(search))
            );
            displayVehicles(filtered);
        }

        async function showModal() {
            editingId = null;
            document.getElementById('modalTitle').textContent = 'Nuevo Vehículo';
            document.getElementById('vehicleForm').reset();
            document.getElementById('vehicleId').value = '';
            await loadCustomers();
            document.getElementById('modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        async function editVehicle(id) {
            try {
                const vehicle = await apiRequest(`vehicles/${id}`);
                editingId = id;

                await loadCustomers();

                document.getElementById('modalTitle').textContent = 'Editar Vehículo';
                document.getElementById('vehicleId').value = vehicle.id;
                document.getElementById('customer_id').value = vehicle.customer_id;
                document.getElementById('plate').value = vehicle.plate;
                document.getElementById('brand').value = vehicle.brand;
                document.getElementById('model').value = vehicle.model;
                document.getElementById('year').value = vehicle.year || '';
                document.getElementById('vin').value = vehicle.vin || '';
                document.getElementById('color').value = vehicle.color || '';
                document.getElementById('mileage').value = vehicle.mileage || '';

                document.getElementById('modal').style.display = 'block';
            } catch (error) {
                showError('Error al cargar vehículo: ' + error.message);
            }
        }

        async function deleteVehicle(id) {
            if (!confirm('¿Estás seguro de eliminar este vehículo?')) return;

            try {
                await apiRequest(`vehicles/${id}`, 'DELETE');
                showSuccess('Vehículo eliminado exitosamente');
                loadVehicles();
            } catch (error) {
                showError('Error al eliminar vehículo: ' + error.message);
            }
        }

        document.getElementById('vehicleForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                customer_id: document.getElementById('customer_id').value,
                plate: document.getElementById('plate').value,
                brand: document.getElementById('brand').value,
                model: document.getElementById('model').value,
                year: document.getElementById('year').value || null,
                vin: document.getElementById('vin').value || null,
                color: document.getElementById('color').value || null,
                mileage: document.getElementById('mileage').value || null
            };

            try {
                if (editingId) {
                    await apiRequest(`vehicles/${editingId}`, 'PUT', data);
                    showSuccess('Vehículo actualizado exitosamente');
                } else {
                    await apiRequest('vehicles', 'POST', data);
                    showSuccess('Vehículo creado exitosamente');
                }

                closeModal();
                loadVehicles();
            } catch (error) {
                showError('Error al guardar vehículo: ' + error.message);
            }
        });

        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) {
                closeModal();
            }
        }

        loadVehicles();
    </script>
@endsection
