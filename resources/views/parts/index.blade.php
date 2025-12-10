@extends('layouts.app')

@section('title', 'Repuestos - Taller Mecánico')

@section('content')
    <div class="header">
        <h1><span>🔩</span> Repuestos e Inventario</h1>
        <div class="user-info">
            <a href="{{ route('dashboard') }}" class="back-btn">← Volver al Dashboard</a>
            <span class="user-name" id="userName"></span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="showModal()">
                ➕ Nuevo Repuesto
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar repuestos..." onkeyup="searchParts()">
            </div>
        </div>

        <div class="loader" id="loader">
            <div class="spinner"></div>
            <p>Cargando inventario...</p>
        </div>

        <div id="content" style="display: none;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Stock</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="partsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Repuesto</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="partForm">
                @csrf
                <input type="hidden" id="partId">

                <div class="form-group">
                    <label for="code">Código *</label>
                    <input type="text" id="code" required>
                </div>

                <div class="form-group">
                    <label for="name">Nombre *</label>
                    <input type="text" id="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" rows="2"></textarea>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="stock">Stock Inicial *</label>
                        <input type="number" id="stock" required min="0">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="min_stock">Stock Mínimo *</label>
                        <input type="number" id="min_stock" required min="0">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="purchase_price">Precio Compra *</label>
                        <input type="number" id="purchase_price" required min="0" step="1">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="sale_price">Precio Venta *</label>
                        <input type="number" id="sale_price" required min="0" step="1">
                    </div>
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
        let parts = [];
        let editingId = null;

        // Auth logic
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!token) window.location.href = '{{ route('login') }}';
        document.getElementById('userName').textContent = user.name || 'Usuario';

        async function loadParts() {
            try {
                const response = await apiRequest('parts');
                // Manejar respuesta
                parts = Array.isArray(response) ? response : (response.data || response);
                displayParts(parts);

                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } catch (error) {
                showError('Error al cargar repuestos: ' + error.message);
            }
        }



        function displayParts(data) {
            const tbody = document.getElementById('partsBody');

            if (data.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">No hay repuestos registrados</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(part => {
                // Lógica de stock bajo
                const isLowStock = parseInt(part.stock) <= parseInt(part.min_stock);
                const stockBadge = isLowStock ?
                    `<span style="background: #ffe3e3; color: #d63031; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">BAJO (${part.min_stock})</span>` :
                    `<span style="background: #e3fafc; color: #0984e3; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">OK</span>`;

                return `
            <tr style="${isLowStock ? 'background-color: #fff5f5;' : ''}">
                <td><code>${part.code}</code></td>
                <td><strong>${part.name}</strong><br><small style="color:#888">${part.description || ''}</small></td>
                <td>
                    <span style="font-size: 1.1em; font-weight: bold;">${part.stock}</span>
                </td>
                <td>${formatMoney(part.purchase_price)}</td>
                <td>${formatMoney(part.sale_price)}</td>
                <td>${stockBadge}</td>
                <td>
                    <button class="btn-icon" onclick="editPart(${part.id})" title="Editar">✏️</button>
                    <button class="btn-icon" onclick="deletePart(${part.id})" title="Eliminar">🗑️</button>
                </td>
            </tr>
            `;
            }).join('');
        }

        function searchParts() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filtered = parts.filter(p =>
                p.name.toLowerCase().includes(search) ||
                p.code.toLowerCase().includes(search)
            );
            displayParts(filtered);
        }

        function showModal() {
            editingId = null;
            document.getElementById('modalTitle').textContent = 'Nuevo Repuesto';
            document.getElementById('partForm').reset();
            document.getElementById('partId').value = '';
            document.getElementById('modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        async function editPart(id) {
            try {
                const part = await apiRequest(`parts/${id}`);
                editingId = id;

                document.getElementById('modalTitle').textContent = 'Editar Repuesto';
                document.getElementById('partId').value = part.id;
                document.getElementById('code').value = part.code;
                document.getElementById('name').value = part.name;
                document.getElementById('description').value = part.description || '';
                document.getElementById('stock').value = part.stock;
                document.getElementById('min_stock').value = part.min_stock;
                document.getElementById('purchase_price').value = part.purchase_price;
                document.getElementById('sale_price').value = part.sale_price;

                document.getElementById('modal').style.display = 'block';
            } catch (error) {
                showError('Error al cargar repuesto: ' + error.message);
            }
        }

        async function deletePart(id) {
            if (!confirm('¿Estás seguro de eliminar este repuesto?')) return;

            try {
                await apiRequest(`parts/${id}`, 'DELETE');
                showSuccess('Repuesto eliminado exitosamente');
                loadParts();
            } catch (error) {
                showError('Error al eliminar: ' + error.message);
            }
        }

        document.getElementById('partForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                code: document.getElementById('code').value,
                name: document.getElementById('name').value,
                description: document.getElementById('description').value,
                stock: document.getElementById('stock').value,
                min_stock: document.getElementById('min_stock').value,
                purchase_price: document.getElementById('purchase_price').value,
                sale_price: document.getElementById('sale_price').value
            };

            try {
                if (editingId) {
                    await apiRequest(`parts/${editingId}`, 'PUT', data);
                    showSuccess('Repuesto actualizado');
                } else {
                    await apiRequest('parts', 'POST', data);
                    showSuccess('Repuesto creado');
                }

                closeModal();
                loadParts();
            } catch (error) {
                showError('Error al guardar: ' + error.message);
            }
        });

        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) closeModal();
        }

        loadParts();
    </script>
@endsection
