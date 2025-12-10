<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Factura - Orden #{{ $id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            /* Estilo ticket/factura clásico */
            background: #fff;
            color: #000;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px dashed #000;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
            font-size: 14px;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .client-info {
            margin-bottom: 30px;
            border: 1px solid #000;
            padding: 15px;
        }

        .client-info h3 {
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .row {
            display: flex;
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        th {
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-left: auto;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #000;
            margin-top: 10px;
            padding-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }

        /* Botón solo visible en pantalla */
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }

        button {
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button onclick="window.print()">🖨️ IMPRIMIR FACTURA</button>
        <button onclick="window.close()" style="background: #ccc; color: #000;">CERRAR</button>
    </div>

    <div id="loading">Cargando datos de factura...</div>

    <div id="invoice" style="display: none;">
        <div class="header">
            <h1>TALLER MECÁNICO</h1>
            <p>Dirección del Taller, Asunción, Paraguay</p>
            <p>Tel: (0981) 123-456 | RUC: 80012345-6</p>
            <h2>FACTURA</h2>
        </div>

        <div class="invoice-info">
            <div>
                <strong>Nº Orden:</strong> <span id="orderNumber"></span>
            </div>
            <div>
                <strong>Fecha:</strong> <span id="invoiceDate"></span>
            </div>
        </div>

        <div class="client-info">
            <h3>DATOS DEL CLIENTE</h3>
            <div class="row"><span class="label">Razón Social:</span> <span id="clientName"></span></div>
            <div class="row"><span class="label">RUC / CI:</span> <span id="clientRuc"></span></div>
            <div class="row"><span class="label">Dirección:</span> <span id="clientAddress"></span></div>
            <div class="row"><span class="label">Vehículo:</span> <span id="vehicleInfo"></span></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody id="itemsTable">
                <!-- Items here -->
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal (Exentas):</span>
                <span>Gs. 0</span>
            </div>
            <div class="total-row">
                <span>Subtotal (IVA 5%):</span>
                <span>Gs. 0</span>
            </div>
            <div class="total-row">
                <span>Subtotal (IVA 10%):</span>
                <span id="subtotal10"></span>
            </div>
            <div class="total-row">
                <span>Liquidación IVA 10%:</span>
                <span id="taxValue"></span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL A PAGAR:</span>
                <span id="grandTotal"></span>
            </div>
        </div>

        <div class="footer">
            <p>Gracias por su preferencia</p>
            <p>Original: Cliente | Copia: Archivo Tributario</p>
        </div>
    </div>

    <script>
        const ORDER_ID = "{{ $id }}";

        // Formateador PYG
        const money = (amount) => {
            return new Intl.NumberFormat('es-PY', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount) + ' Gs.';
        };

        async function loadInvoice() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) window.location.href = '/login';

                const response = await fetch(`/api/work-orders/${ORDER_ID}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (!response.ok) throw new Error("Error al cargar orden");

                const json = await response.json();
                console.log("API Response:", json); // DEBUG

                // Manejar si viene envuelto en 'data' o directo
                const order = json.data ? json.data : json;

                if (!order || !order.order_number) {
                    throw new Error("Formato de respuesta inválido o orden no encontrada");
                }

                // 1. Llenar Datos
                document.getElementById('orderNumber').textContent = order.order_number || '---';
                document.getElementById('invoiceDate').textContent = new Date().toLocaleDateString('es-PY');

                // Verificar Vehicle y Customer
                const vehicle = order.vehicle || {};
                const customer = vehicle.customer || {};

                document.getElementById('clientName').textContent = customer.name || 'Cliente Genérico';
                document.getElementById('clientRuc').textContent = customer.document || '---';
                document.getElementById('clientAddress').textContent = customer.address || 'Sin dirección';
                document.getElementById('vehicleInfo').textContent = vehicle.plate ?
                    `${vehicle.brand} ${vehicle.model} - ${vehicle.plate}` :
                    'Vehículo no especificado';

                // 2. Llenar Items
                const tbody = document.getElementById('itemsTable');
                let html = '';

                // Servicios
                if (order.services) {
                    order.services.forEach(svc => {
                        html += `
                            <tr>
                                <td>[SERVICIO] ${svc.name}</td>
                                <td class="text-right">1</td>
                                <td class="text-right">${money(svc.price)}</td>
                                <td class="text-right">${money(svc.price)}</td>
                            </tr>
                        `;
                    });
                }

                // Repuestos (La API devuelve 'parts' con la estructura del recurso)
                if (order.parts) {
                    order.parts.forEach(p => {
                        // p es el objeto workOrderPart transformado
                        html += `
                            <tr>
                                <td>[REPUESTO] ${p.part.name}</td>
                                <td class="text-right">${p.quantity}</td>
                                <td class="text-right">${money(p.unit_price)}</td>
                                <td class="text-right">${money(p.subtotal_price)}</td>
                            </tr>
                        `;
                    });
                }

                tbody.innerHTML = html;

                // 3. Totales
                const total = parseFloat(order.prices.total);
                const vat10 = Math.round(total / 11);

                document.getElementById('subtotal10').textContent = money(total);
                document.getElementById('taxValue').textContent = money(vat10);
                document.getElementById('grandTotal').textContent = money(total);

                document.getElementById('loading').style.display = 'none';
                document.getElementById('invoice').style.display = 'block';

                // Imprimir automático si se desea
                // window.print();

            } catch (error) {
                console.error('Error Invoice:', error);
                document.getElementById('loading').innerHTML = `
                    <div style="color:red; text-align:center">
                        <h3>❌ Error al cargar factura</h3>
                        <p>${error.message}</p>
                        <hr>
                        <p>Detalles técnicos (Mostrar al administrador):</p>
                        <pre style="text-align:left; background:#eee; padding:10px; overflow:auto;">${error.stack}</pre>
                    </div>
                `;
            }
        }

        loadInvoice();
    </script>
</body>

</html>
