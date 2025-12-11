<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión Factura</title>
    <style>
        @media print {
            @page { margin: 0; size: auto; }
            body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; }
        }
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente monoospaciada para mejor alineación */
            font-size: 14px;
            width: 21cm; /* Ancho A4 Aprox */
            height: 29.7cm;
            position: relative;
        }
        .field {
            position: absolute;
            white-space: nowrap;
            /* debug border: 1px solid red;  <-- Descomentar para ver cajas */
        }

        /* --- CONFIGURACION DE POSICIONES (Ajustar Top y Left en cm o mm) --- */

        /* CABECERA */
        .date-day { top: 4.5cm; left: 2.5cm; }
        .date-month { top: 4.5cm; left: 4.5cm; }
        .date-year { top: 4.5cm; left: 8.0cm; }

        .cond-contado { top: 4.5cm; left: 14.5cm; } /* X si es contado */
        .cond-credito { top: 4.5cm; left: 17.5cm; } /* X si es credito */

        .client-ruc { top: 5.2cm; left: 2.5cm; }
        .client-phone { top: 5.2cm; left: 14.0cm; }

        .client-name { top: 5.9cm; left: 4.0cm; }
        .client-address { top: 6.6cm; left: 2.5cm; }

        /* DETALLE (ITEMS) */
        .items-container {
            position: absolute;
            top: 8.5cm;
            left: 0.5cm;
            width: 20cm;
        }
        .item-row {
            height: 0.6cm; /* Altura de cada fila impresa */
            display: flex;
            align-items: center;
        }
        /* Defino anchos fijos para columnas manuales */
        .col-qty { width: 1.5cm; text-align: center; }
        .col-desc { width: 9.5cm; padding-left: 5px; }
        .col-price { width: 2.5cm; text-align: right; }
        .col-exenta { width: 2.0cm; text-align: right; }
        .col-iva5 { width: 2.0cm; text-align: right; }
        .col-iva10 { width: 2.0cm; text-align: right; } /* Ajustar */

        /* PIE DE PAGINA */
        .total-number { top: 22.0cm; left: 17.0cm; font-weight: bold; }
        .total-text { top: 21.3cm; left: 1.5cm; }

        .liq-5 { top: 23.0cm; left: 4.0cm; }
        .liq-10 { top: 23.0cm; left: 8.0cm; }
        .liq-total { top: 23.0cm; left: 14.0cm; }

    </style>
</head>
<body onload="window.print()">

    @php
        $d = \Carbon\Carbon::parse($order->entry_date); // O fecha factura?
    @endphp

    <!-- FECHA -->
    <div class="field date-day">{{ $d->format('d') }}</div>
    <div class="field date-month">{{ $order->month_text ?? $d->format('M') }}</div>
    <div class="field date-year">{{ $d->format('Y') }}</div>

    <!-- CONDICION VENTA (De momento Contado siempre o logica?) -->
    <div class="field cond-contado">X</div>

    <!-- CLIENTE -->
    <div class="field client-ruc">{{ $order->vehicle->customer->document }}</div>
    <div class="field client-name">{{ $order->vehicle->customer->name }}</div>
    <div class="field client-address">{{ $order->vehicle->customer->address ?? 'Asunción' }}</div>
    <div class="field client-phone">{{ $order->vehicle->customer->phone }}</div>

    <!-- ITEMS -->
    <div class="items-container">
        <!-- Servicios -->
        @foreach($order->services as $svc)
        <div class="item-row">
            <div class="col-qty">1</div>
            <div class="col-desc">{{ Str::limit($svc->name, 40) }}</div>
            <div class="col-price">{{ number_format($svc->price, 0, ',', '.') }}</div>
            <div class="col-exenta"></div>
            <div class="col-iva5"></div>
            <div class="col-iva10">{{ number_format($svc->price, 0, ',', '.') }}</div>
        </div>
        @endforeach

        <!-- Repuestos -->
        @foreach($order->parts as $part)
        <div class="item-row">
            <div class="col-qty">{{ $part->pivot->quantity }}</div>
            <div class="col-desc">{{ Str::limit($part->name, 40) }}</div>
            <div class="col-price">{{ number_format($part->pivot->unit_price, 0, ',', '.') }}</div>
            <div class="col-exenta"></div>
            <div class="col-iva5"></div>
            <div class="col-iva10">{{ number_format($part->pivot->subtotal_price, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>

    <!-- TOTALES -->
    <div class="field total-text">Son Guaraníes: {{ $numberToWords }}</div> <!-- Necesitaria un helper para esto -->

    <div class="field total-number">{{ number_format($order->total_price, 0, ',', '.') }}</div>

    <!-- LIQUIDACION IVA (Asumiendo todo 10%) -->
    @php
        $iva10 = round($order->total_price / 11);
    @endphp
    <div class="field liq-10">{{ number_format($iva10, 0, ',', '.') }}</div>
    <div class="field liq-total">{{ number_format($iva10, 0, ',', '.') }}</div>

</body>
</html>
