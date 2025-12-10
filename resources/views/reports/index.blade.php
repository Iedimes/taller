@extends('layouts.app')

@section('title', 'Reportes - Taller Mecánico')

@section('styles')
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .report-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .report-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            font-weight: bold;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f9f9f9;
            font-size: 14px;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .badge-rank {
            background: #667eea;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            margin-right: 8px;
        }

        .date-filter {
            background: white;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
    </style>
@endsection

@section('content')
    <div class="header">
        <h1><span>📈</span> Reportes y Análisis</h1>
        <div class="user-info">
            <a href="{{ route('dashboard') }}" class="back-btn">← Volver al Dashboard</a>
            <span class="user-name" id="userName"></span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">

        <div class="date-filter">
            <div class="form-group" style="margin:0">
                <label>Fecha Inicio</label>
                <input type="date" id="startDate">
            </div>
            <div class="form-group" style="margin:0">
                <label>Fecha Fin</label>
                <input type="date" id="endDate">
            </div>
            <div class="form-group" style="margin:0">
                <label>Vehículo (Opcional)</label>
                <select id="vehicleFilter" style="padding:8px; border:1px solid #ddd; border-radius:5px; width:150px">
                    <option value="">Todos</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="loadSales()">Filtrar Ventas</button>
        </div>

        <!-- Inventory Section -->
        <h2 class="section-title" style="margin-top: 30px;">📦 Inventario</h2>
        <div class="report-grid">
            <div class="report-card">
                <h3 class="report-title">Estado del Inventario</h3>
                <div id="inventoryStats">Cargando...</div>
            </div>
            <div class="report-card">
                <h3 class="report-title">Repuestos Stock Bajo ⚠️</h3>
                <div id="lowStockList">Cargando...</div>
            </div>
        </div>

        <!-- Sales Section -->
        <h2 class="section-title" style="margin-top: 30px;">💰 Ventas y Rentabilidad</h2>
        <div class="report-grid">
            <div class="report-card">
                <h3 class="report-title">Resumen Financiero</h3>
                <div id="salesSummary">Seleccione fechas para ver datos...</div>
            </div>
            <div class="report-card">
                <h3 class="report-title">Eficiencia Operativa</h3>
                <div id="efficiencyStats">Cargando...</div>
            </div>
        </div>

        <!-- Rankings Section -->
        <h2 class="section-title" style="margin-top: 30px;">🏆 Rankings</h2>
        <div class="report-grid">
            <div class="report-card">
                <h3 class="report-title">Top Clientes</h3>
                <div id="topCustomers">Cargando...</div>
            </div>
            <div class="report-card">
                <h3 class="report-title">Top Repuestos</h3>
                <div id="topParts">Cargando...</div>
            </div>
            <div class="report-card">
                <h3 class="report-title">Top Servicios</h3>
                <div id="topServices">Cargando...</div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        // Auth
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!token) window.location.href = '{{ route('login') }}';
        document.getElementById('userName').textContent = user.name || 'Usuario';

        // Format helpers


        // Set default dates (current month)
        const date = new Date();
        document.getElementById('endDate').valueAsDate = date;
        document.getElementById('startDate').valueAsDate = new Date(date.getFullYear(), date.getMonth(), 1);

        async function loadAllReports() {
            loadVehicleOptions();
            loadInventory();
            loadEfficiency();
            loadRankings();
            loadSales(); // Initial load
        }

        async function loadVehicleOptions() {
            try {
                const vehicles = await apiRequest('vehicles'); // Assuming endpoint returns all vehicles
                const select = document.getElementById('vehicleFilter');
                // Si la respuesta es {data: [...]}, manejamos. Si es [...] directo, también.
                const list = vehicles.data || vehicles;

                list.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = `${v.plate} - ${v.brand} ${v.model}`;
                    select.appendChild(opt);
                });
            } catch (e) {
                console.error("Error loading vehicles filter", e);
            }
        }

        async function loadInventory() {
            try {
                const data = await apiRequest('reports/inventory-analysis');

                document.getElementById('inventoryStats').innerHTML = `
                <div class="list-item"><span>Total Repuestos</span> <strong>${data.summary.total_parts}</strong></div>
                <div class="list-item"><span>Valor Total</span> <strong>${formatMoney(data.summary.total_value)}</strong></div>
                <div class="list-item"><span>Potencial Venta</span> <strong>${formatMoney(data.summary.potential_revenue)}</strong></div>
            `;

                const lowStock = data.stock_levels.low.parts || [];
                if (lowStock.length === 0) {
                    document.getElementById('lowStockList').innerHTML =
                        '<p style="color:green; text-align:center">Todo en orden ✅</p>';
                } else {
                    document.getElementById('lowStockList').innerHTML = lowStock.slice(0, 5).map(p => `
                    <div class="list-item" style="color:#d63031">
                        <span>${p.name}</span>
                        <strong>${p.stock} / ${p.min_stock}</strong>
                    </div>
                `).join('') + (lowStock.length > 5 ?
                        `<div style="text-align:center; margin-top:10px; font-size:12px">...y ${lowStock.length - 5} más</div>` :
                        '');
                }

            } catch (e) {
                console.error(e);
            }
        }

        async function loadEfficiency() {
            try {
                const data = await apiRequest('reports/efficiency');
                document.getElementById('efficiencyStats').innerHTML = `
                <div class="list-item"><span>A tiempo</span> <strong>${data.summary.on_time_percentage}%</strong></div>
                <div class="list-item"><span>Promedio dias (Est)</span> <strong>${data.average_days.estimated}</strong></div>
                <div class="list-item"><span>Promedio dias (Real)</span> <strong>${data.average_days.actual}</strong></div>
            `;
            } catch (e) {
                console.error(e);
            }
        }

        async function loadRankings() {
            try {
                // Customers
                const customers = await apiRequest('reports/top-customers?limit=5');
                document.getElementById('topCustomers').innerHTML = customers.top_customers.map((c, i) => `
                <div class="list-item">
                    <span><span class="badge-rank">${i+1}</span> ${c.name}</span>
                    <strong>${formatMoney(c.total_profit)}</strong>
                </div>
            `).join('');

                // Parts
                const parts = await apiRequest('reports/top-parts?limit=5');
                document.getElementById('topParts').innerHTML = parts.top_parts.map((p, i) => `
                <div class="list-item">
                     <span><span class="badge-rank">${i+1}</span> ${p.name}</span>
                    <strong>${p.total_sold} un.</strong>
                </div>
            `).join('');

                // Services
                const services = await apiRequest('reports/top-services?limit=5');
                document.getElementById('topServices').innerHTML = services.top_services.map((s, i) => `
                <div class="list-item">
                     <span><span class="badge-rank">${i+1}</span> ${s.name}</span>
                    <strong>${s.times_performed} veces</strong>
                </div>
            `).join('');

            } catch (e) {
                console.error(e);
            }
        }

        async function loadSales() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            const vehicleId = document.getElementById('vehicleFilter') ? document.getElementById('vehicleFilter')
                .value : '';

            document.getElementById('salesSummary').innerHTML =
                '<div class="spinner" style="width:20px;height:20px;border-width:2px"></div>';

            try {
                let url = `reports/sales?start_date=${start}&end_date=${end}`;
                if (vehicleId) url += `&vehicle_id=${vehicleId}`;

                const data = await apiRequest(url);

                // Summary
                document.getElementById('salesSummary').innerHTML = `
                <div class="list-item"><span>Total Órdenes</span> <strong>${data.summary.count}</strong></div>
                <div class="list-item"><span>Ingresos Totales</span> <strong>${formatMoney(data.summary.total_price)}</strong></div>
                <div class="list-item"><span>Costos Totales</span> <strong>${formatMoney(data.summary.total_cost)}</strong></div>
                <div class="list-item" style="color:#27ae60; font-size:1.1em; background:#f0fff4">
                    <span>Utildiad Neta</span> <strong>${formatMoney(data.summary.total_profit)}</strong>
                </div>
                <div style="margin-top:10px; font-size:12px; color:#666; text-align:center">
                    Ticket Promedio: <strong>${formatMoney(data.summary.average_ticket)}</strong>
                </div>
            `;

                // Daily Breakdown Table
                let dailyHtml = `
                <h3 class="report-title" style="margin-top:20px">📅 Desglose Diario</h3>
                <div style="max-height:200px; overflow-y:auto">
                    <table style="width:100%; font-size:13px; text-align:left">
                        <thead style="background:#f8f9fa; position:sticky; top:0">
                            <tr><th>Fecha</th><th style="text-align:right">Ingreso</th><th style="text-align:right">Utilidad</th></tr>
                        </thead>
                        <tbody>
                            ${data.daily_breakdown.map(d => `
                                            <tr>
                                                <td style="padding:5px">${d.date}</td>
                                                <td style="padding:5px; text-align:right">${formatMoney(d.revenue)}</td>
                                                <td style="padding:5px; text-align:right; font-weight:bold; color:${d.profit>0?'green':'red'}">${formatMoney(d.profit)}</td>
                                            </tr>
                                        `).join('')}
                        </tbody>
                    </table>
                </div>`;

                // Detailed Orders
                let ordersHtml = `
                 <h3 class="report-title" style="margin-top:20px">📄 Detalle de Órdenes</h3>
                 <div style="max-height:300px; overflow-y:auto">
                    ${data.work_orders.map(o => `
                                    <div style="border-bottom:1px solid #eee; padding:8px 0; font-size:13px">
                                        <div style="display:flex; justify-content:space-between">
                                            <strong>#${o.order_number} - ${o.vehicle.brand} ${o.vehicle.model}</strong>
                                            <span>${formatMoney(o.total_price)}</span>
                                        </div>
                                        <div style="display:flex; justify-content:space-between; color:#666; font-size:11px">
                                            <span>${o.vehicle.plate} - ${new Date(o.entry_date).toLocaleDateString()}</span>
                                            <span style="color:${o.profit>0?'green':'red'}">Util: ${formatMoney(o.profit)}</span>
                                        </div>
                                    </div>
                                `).join('')}
                 </div>
                `;

                // Insert into DOM (assuming we have a container, current implementation only has salesSummary)
                // We will append to salesSummary div for now or replace the content appropriately

                // Hack: Expand salesSummary container to fit more data
                const container = document.getElementById('salesSummary');
                // Mantener el resumen arriba y agregar lo demás
                const summaryHtml = container.innerHTML;
                container.innerHTML = summaryHtml + dailyHtml + ordersHtml;


            } catch (e) {
                document.getElementById('salesSummary').innerHTML = 'Error al cargar datos.';
                console.error(e);
            }
        }


        loadAllReports();
    </script>
@endsection
