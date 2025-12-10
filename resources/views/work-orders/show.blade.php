@extends('layouts.app')

@section('title', 'Detalle de Orden - Taller Mecánico')

@section('styles')
    <style>
        .order-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .info-card h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #667eea;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .items-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .add-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .total-display {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            text-align: right;
            margin-top: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="header">
        <h1><span>🔧</span> Orden <span id="orderNumberTitle"></span></h1>
        <div class="user-info">
            <a href="{{ route('work-orders') }}" class="back-btn">← Volver</a>
            <a href="" target="_blank" id="invoiceBtn" class="back-btn">🖨️ Factura</a>
        </div>
    </div>

    <div class="container">
        <!-- Loader -->
        <div id="loader" style="text-align: center; padding: 40px;">
            <div class="spinner"
                style="border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto;">
            </div>
            <p>Cargando detalles...</p>
        </div>

        <div id="content" style="display: none;">

            <div class="info-grid">
                <!-- Cliente y Vehículo -->
                <div class="info-card">
                    <h3>🚗 Vehículo y Cliente</h3>
                    <div class="info-row"><span class="info-label">Cliente:</span> <span id="clientName"></span></div>
                    <div class="info-row"><span class="info-label">RUC:</span> <span id="clientRuc"></span></div>
                    <div class="info-row"><span class="info-label">Vehículo:</span> <span id="vehicleData"></span></div>
                    <div class="info-row"><span class="info-label">Kilometraje:</span> <span id="vehicleMileage"></span>
                    </div>
                </div>

                <!-- Estado y Fechas -->
                <div class="info-card">
                    <h3>📅 Estado y Fechas</h3>
                    <div class="info-row"><span class="info-label">Estado:</span> <span id="orderStatus"></span></div>
                    <div class="info-row"><span class="info-label">Ingreso:</span> <span id="entryDate"></span></div>
                    <div class="info-row"><span class="info-label">Entrega Est.:</span> <span id="estDate"></span></div>
                    <div class="info-row"><span class="info-label">Descripción:</span>
                        <p id="descriptionText" style="margin:0; text-align:right; font-style:italic"></p>
                    </div>
                </div>
            </div>

            <!-- SERVICIOS -->
            <div class="items-section">
                <div class="section-header">
                    <h3>🛠️ Servicios / Mano de Obra</h3>
                    <button class="add-btn" id="btnAddService" onclick="openServiceModal()">➕ Agregar Servicio</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Costo (Interno)</th>
                            <th>Precio (Cliente)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="servicesBody"></tbody>
                </table>
            </div>

            <!-- REPUESTOS -->
            <div class="items-section">
                <div class="section-header">
                    <h3>🔩 Repuestos</h3>
                    <button class="add-btn" id="btnAddPart" style="background:#2980b9" onclick="openPartModal()">➕ Agregar
                        Repuesto</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Repuesto</th>
                            <th>Cant.</th>
                            <th>Precio Unit.</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="partsBody"></tbody>
                </table>
            </div>

            <!-- TOTALES -->
            <div class="info-card" style="background: #f8f9fa;">
                <div class="info-row">
                    <span class="info-label" style="font-size:18px">Total Servicios:</span>
                    <span id="totalServices" style="font-size:18px"></span>
                </div>
                <div class="info-row">
                    <span class="info-label" style="font-size:18px">Total Repuestos:</span>
                    <span id="totalParts" style="font-size:18px"></span>
                </div>
                <hr>
                <div class="total-display">
                    TOTAL: <span id="grandTotal" style="color:#27ae60"></span>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL SERVICIO -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="svcModalTitle">Agregar Servicio</h2>
                <span class="close" onclick="closeModal('serviceModal')">&times;</span>
            </div>
            <form id="serviceForm">
                <input type="hidden" id="svc_id">
                <div class="form-group">
                    <label>Nombre del Servicio / Trabajo *</label>
                    <input type="text" id="svc_name" required placeholder="Ej: Cambio de Aceite">
                </div>
                <div class="form-group">
                    <label>Precio al Cliente (Guaraníes) *</label>
                    <input type="number" id="svc_price" required min="0" step="1" placeholder="Ej: 150000">
                </div>
                <div class="form-group">
                    <label>Costo de Mano de Obra (Interno)</label>
                    <input type="number" id="svc_cost" min="0" step="1" value="0"
                        placeholder="Costo para el taller">
                    <small>Opcional: Lo que le pagas al mecánico.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('serviceModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL REPUESTO -->
    <div id="partModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="partModalTitle">Agregar Repuesto</h2>
                <span class="close" onclick="closeModal('partModal')">&times;</span>
            </div>
            <form id="partForm">
                <input type="hidden" id="part_item_id"> <!-- ID de la relación WorkOrderPart -->
                <div class="form-group">
                    <label>Seleccionar Repuesto *</label>
                    <div id="partSelectContainer">
                        <select id="part_id" required onchange="updatePartPrice()">
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <input type="text" id="part_name_display" disabled style="display:none; background:#eee">
                </div>
                <div class="form-group" style="display:flex; gap:10px">
                    <div style="flex:1">
                        <label>Cantidad *</label>
                        <input type="number" id="part_qty" value="1" min="1" required>
                    </div>
                    <div style="flex:1">
                        <label>Stock Disponible</label>
                        <input type="text" id="part_stock" disabled style="background:#eee">
                    </div>
                </div>
                <div class="form-group">
                    <label>Precio Unitario (Guaraníes) *</label>
                    <input type="number" id="part_price" required min="0" step="1">
                    <small>Se carga autom. del inventario pero puedes modificarlo.</small>
                </div>
                <div class="form-group">
                    <label>Costo Unitario (Interno)</label>
                    <input type="number" id="part_cost" required min="0" step="1" readonly
                        style="background:#eee">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('partModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        const ORDER_ID = "{{ $id }}";
        let currentOrder = null;
        let availableParts = [];
        let isReadOnly = false;

        // Auth & Init
        const token = localStorage.getItem('auth_token');
        if (!token) window.location.href = '/login';

        // Agregar estilo de loader si no existe
        const style = document.createElement('style');
        style.innerHTML = `@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }`;
        document.head.appendChild(style);

        async function loadOrderDetails() {
            try {
                const response = await apiRequest(`work-orders/${ORDER_ID}`);
                currentOrder = response.data || response;

                // Check Read Only Status
                isReadOnly = ['delivered', 'cancelled'].includes(currentOrder.status);

                renderOrder();

                // Set invoice link
                document.getElementById('invoiceBtn').href = `/work-orders/${ORDER_ID}/invoice`;

                document.getElementById('loader').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } catch (error) {
                showError("Error al cargar orden: " + error.message);
            }
        }

        function renderOrder() {
            const o = currentOrder;

            // Header
            document.getElementById('orderNumberTitle').textContent = `#${o.order_number}`;

            // Info
            document.getElementById('clientName').textContent = o.vehicle.customer.name;
            document.getElementById('clientRuc').textContent = o.vehicle.customer.document || '-';
            document.getElementById('vehicleData').textContent =
                `${o.vehicle.brand} ${o.vehicle.model} (${o.vehicle.plate})`;
            document.getElementById('vehicleMileage').textContent = o.vehicle.mileage ? `${o.vehicle.mileage} km` : '-';

            document.getElementById('orderStatus').innerHTML = getStatusBadge(o.status);
            document.getElementById('entryDate').textContent = o.dates.entry;
            document.getElementById('estDate').textContent = o.dates.estimated_delivery || '-';
            document.getElementById('descriptionText').textContent = o.description;

            // Mostrar/Ocultar botones de agregar
            document.getElementById('btnAddService').style.display = isReadOnly ? 'none' : 'block';
            document.getElementById('btnAddPart').style.display = isReadOnly ? 'none' : 'block';

            // Totales parciales
            let totalServices = 0;
            let totalParts = 0;

            // Servicios Table
            const svcBody = document.getElementById('servicesBody');
            if (o.services && o.services.length > 0) {
                svcBody.innerHTML = o.services.map(s => {
                    totalServices += parseFloat(s.price);
                    const editBtn = !isReadOnly ?
                        `<button class="btn-icon" onclick="openServiceModal(${s.id})" title="Editar">✏️</button>` :
                        '';
                    const delBtn = !isReadOnly ?
                        `<button class="btn-icon" onclick="removeService(${s.id})" title="Eliminar">🗑️</button>` :
                        '';

                    return `
                    <tr>
                        <td>${s.name}</td>
                        <td>${formatMoney(s.cost)}</td>
                        <td><strong>${formatMoney(s.price)}</strong></td>
                        <td>${editBtn} ${delBtn}</td>
                    </tr>`;
                }).join('');
            } else {
                svcBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#999">Sin servicios</td></tr>';
            }

            // Partes Table
            const partsBody = document.getElementById('partsBody');
            if (o.parts && o.parts.length > 0) {
                partsBody.innerHTML = o.parts.map(p => {
                    totalParts += parseFloat(p.subtotal_price);
                    const editBtn = !isReadOnly ?
                        `<button class="btn-icon" onclick="openPartModal(${p.id})" title="Editar">✏️</button>` :
                        '';
                    const delBtn = !isReadOnly ?
                        `<button class="btn-icon" onclick="removePart(${p.id})" title="Eliminar">🗑️</button>` :
                        '';

                    return `
                    <tr>
                        <td>${p.part.name} <small style="color:#666">(${p.part.code})</small></td>
                        <td>${p.quantity}</td>
                        <td>${formatMoney(p.unit_price)}</td>
                        <td><strong>${formatMoney(p.subtotal_price)}</strong></td>
                        <td>${editBtn} ${delBtn}</td>
                    </tr>`;
                }).join('');
            } else {
                partsBody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999">Sin repuestos</td></tr>';
            }

            // Totales Footer
            document.getElementById('totalServices').textContent = formatMoney(totalServices);
            document.getElementById('totalParts').textContent = formatMoney(totalParts);
            document.getElementById('grandTotal').textContent = formatMoney(o.prices.total);
        }

        // --- Services Logic ---
        function openServiceModal(id = null) {
            document.getElementById('serviceForm').reset();
            document.getElementById('svc_id').value = '';

            if (id) {
                document.getElementById('svcModalTitle').textContent = "Editar Servicio";
                const s = currentOrder.services.find(x => x.id === id);
                if (s) {
                    document.getElementById('svc_id').value = s.id;
                    document.getElementById('svc_name').value = s.name;
                    document.getElementById('svc_price').value = Math.round(s.price);
                    document.getElementById('svc_cost').value = Math.round(s.cost);
                }
            } else {
                document.getElementById('svcModalTitle').textContent = "Agregar Servicio";
            }

            document.getElementById('serviceModal').style.display = 'block';
        }

        document.getElementById('serviceForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('svc_id').value;
            const data = {
                name: document.getElementById('svc_name').value,
                price: document.getElementById('svc_price').value,
                cost: document.getElementById('svc_cost').value || 0
            };

            try {
                if (id) {
                    await apiRequest(`work-orders/${ORDER_ID}/services/${id}`, 'PUT', data);
                    showSuccess("Servicio actualizado");
                } else {
                    await apiRequest(`work-orders/${ORDER_ID}/services`, 'POST', data);
                    showSuccess("Servicio agregado");
                }
                closeModal('serviceModal');
                loadOrderDetails();
            } catch (error) {
                showError(error.message);
            }
        });

        async function removeService(id) {
            if (!confirm("¿Eliminar este servicio?")) return;
            try {
                await apiRequest(`work-orders/${ORDER_ID}/services/${id}`, 'DELETE');
                loadOrderDetails();
            } catch (e) {
                showError(e.message);
            }
        }

        // --- Parts Logic ---
        async function openPartModal(id = null) {
            document.getElementById('partForm').reset();
            document.getElementById('part_item_id').value = '';

            // Load parts if not loaded
            if (availableParts.length === 0) {
                try {
                    const res = await apiRequest('parts');
                    availableParts = Array.isArray(res) ? res : (res.data || res);
                    const select = document.getElementById('part_id');
                    select.innerHTML = '<option value="">Seleccione...</option>' +
                        availableParts.map(p => `<option value="${p.id}">${p.name} (Stock: ${p.stock})</option>`).join(
                            '');
                } catch (e) {
                    showError("Error al cargar inventario");
                    return;
                }
            }

            if (id) {
                // EDIT MODE
                document.getElementById('partModalTitle').textContent = "Editar Cantidad/Precio";
                const p = currentOrder.parts.find(x => x.id === id);
                if (p) {
                    document.getElementById('part_item_id').value = p.id;

                    // En edición, desactivamos requirement del select oculto
                    const partSelect = document.getElementById('part_id');
                    partSelect.required = false;

                    document.getElementById('partSelectContainer').style.display = 'none';
                    const nameDisplay = document.getElementById('part_name_display');
                    nameDisplay.style.display = 'block';
                    nameDisplay.value = p.part.name;

                    // Llenamos datos para que calcule stock
                    const dbPart = availableParts.find(x => x.id == p.part.id);
                    document.getElementById('part_stock').value = dbPart ? dbPart.stock : '?';

                    document.getElementById('part_qty').value = p.quantity;
                    document.getElementById('part_price').value = Math.round(p.unit_price);
                    document.getElementById('part_cost').value = Math.round(p.unit_cost);
                }
            } else {
                // CREATE MODE
                document.getElementById('partModalTitle').textContent = "Agregar Repuesto";

                const partSelect = document.getElementById('part_id');
                partSelect.required = true;
                partSelect.value = "";

                document.getElementById('partSelectContainer').style.display = 'block';
                document.getElementById('part_name_display').style.display = 'none';
            }

            document.getElementById('partModal').style.display = 'block';
        }

        function updatePartPrice() {
            // Solo aplica en Create Mode
            const id = document.getElementById('part_id').value;
            const part = availableParts.find(p => p.id == id);
            if (part) {
                document.getElementById('part_price').value = Math.round(part.sale_price); // Integer for Gs
                document.getElementById('part_cost').value = Math.round(part.purchase_price);
                document.getElementById('part_stock').value = part.stock;
            }
        }

        document.getElementById('partForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('part_item_id').value;

            const data = {
                quantity: document.getElementById('part_qty').value,
                unit_price: document.getElementById('part_price').value,
                unit_cost: document.getElementById('part_cost').value
            };

            try {
                if (id) {
                    await apiRequest(`work-orders/${ORDER_ID}/parts/${id}`, 'PUT', data);
                    showSuccess("Repuesto actualizado");
                } else {
                    data.part_id = document.getElementById('part_id').value;
                    await apiRequest(`work-orders/${ORDER_ID}/parts`, 'POST', data);
                    showSuccess("Repuesto agregado");
                }
                closeModal('partModal');
                loadOrderDetails();
            } catch (error) {
                showError(error.message);
            }
        });

        async function removePart(id) {
            if (!confirm("¿Eliminar este repuesto y devolver al stock?")) return;
            try {
                await apiRequest(`work-orders/${ORDER_ID}/parts/${id}`, 'DELETE');
                loadOrderDetails();
            } catch (e) {
                showError(e.message);
            }
        }

        // --- Utils ---
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
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
            return `<span style="background:${s.color}20; color:${s.color}; padding: 4px 10px; border-radius: 20px; font-weight:bold">${s.text}</span>`;
        }

        loadOrderDetails();
    </script>
@endsection
