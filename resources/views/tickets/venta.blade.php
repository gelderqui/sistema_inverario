<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 10px; }
        .center { text-align: center; }
        .strong { font-weight: 700; }
        .muted { color: #4b5563; }
        .title { font-size: 13px; font-weight: 700; margin-bottom: 2px; }
        .section { margin-top: 8px; padding-top: 6px; border-top: 1px dashed #9ca3af; }
        .row { width: 100%; margin-bottom: 3px; }
        .label { display: inline-block; width: 74px; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { padding: 2px 0; vertical-align: top; }
        thead th { border-bottom: 1px solid #9ca3af; font-size: 10px; text-transform: uppercase; }
        .num { text-align: right; white-space: nowrap; }
        .totals .line { display: flex; justify-content: space-between; margin-top: 2px; }
        .footer { margin-top: 10px; border-top: 1px dashed #9ca3af; padding-top: 8px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center">
        <div class="title">{{ $empresa['nombre'] ?? 'Sistema POS e Inventario' }}</div>
        @if(!empty($empresa['direccion']))
            <div class="muted">{{ $empresa['direccion'] }}</div>
        @endif
        @if(!empty($empresa['telefono']))
            <div class="muted">Tel: {{ $empresa['telefono'] }}</div>
        @endif
        <div class="strong" style="margin-top: 5px;">RECIBO DE VENTA</div>
    </div>

    <div class="section">
        <div class="row"><span class="label">Numero:</span> <span class="strong">{{ $venta->numero }}</span></div>
        <div class="row"><span class="label">Fecha:</span> {{ optional($venta->fecha_venta)->format('d/m/Y') }}</div>
        <div class="row"><span class="label">Cliente:</span> {{ $venta->cliente->nombre ?? 'Consumidor final' }}</div>
        <div class="row"><span class="label">Pago:</span> {{ strtoupper((string) $venta->metodo_pago) }}</div>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Descripcion</th>
                    <th class="num">Cant</th>
                    <th class="num">P.Unit</th>
                    <th class="num">Subt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $item)
                    <tr>
                        <td>{{ $item->producto->nombre ?? 'Producto' }}</td>
                        <td class="num">{{ number_format((float) $item->cantidad, 2) }}</td>
                        <td class="num">Q {{ number_format((float) $item->precio_unitario, 2) }}</td>
                        <td class="num">Q {{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section totals">
        <div class="line"><span>Subtotal</span><span>Q {{ number_format((float) $venta->subtotal, 2) }}</span></div>
        <div class="line"><span>Descuento</span><span>Q {{ number_format((float) $venta->descuento, 2) }}</span></div>
        <div class="line strong"><span>Total</span><span>Q {{ number_format((float) $venta->total, 2) }}</span></div>
        @if(!is_null($venta->monto_recibido))
            <div class="line"><span>Recibido</span><span>Q {{ number_format((float) $venta->monto_recibido, 2) }}</span></div>
        @endif
        @if(!is_null($venta->cambio))
            <div class="line"><span>Cambio</span><span>Q {{ number_format((float) $venta->cambio, 2) }}</span></div>
        @endif
    </div>

    <div class="footer">
        <div>Fecha impresion: {{ now()->format('d/m/Y H:i') }}</div>
        <div>Gracias por su compra.</div>
    </div>
</body>
</html>
