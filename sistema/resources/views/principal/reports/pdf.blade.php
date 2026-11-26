<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de ventas - FARM SJB</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #343a40; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .subtitle { color: #6c757d; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #dee2e6; padding: 6px; }
    th { background: #f8f9fa; text-align: left; }
    .text-end { text-align: right; }
    .summary { margin: 12px 0 16px; }
  </style>
</head>
<body>
  <h1>FARM SJB · Reporte de ventas</h1>
  <div class="subtitle">Desde {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} hasta {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</div>

  <div class="summary">
    <strong>Total vendido:</strong> S/ {{ number_format($totalSales, 2) }} &nbsp;&nbsp;
    <strong>Número de ventas:</strong> {{ $salesCount }}
  </div>

  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Vendedor</th>
        <th class="text-end">IGV</th>
        <th class="text-end">Total</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($sales as $sale)
        <tr>
          <td>{{ $sale->date?->format('d/m/Y H:i') }}</td>
          <td>{{ $sale->customer->name ?? 'Sin cliente' }}</td>
          <td>{{ $sale->user->name ?? 'Sin vendedor' }}</td>
          <td class="text-end">S/ {{ number_format($sale->igv, 2) }}</td>
          <td class="text-end">S/ {{ number_format($sale->total, 2) }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5" style="text-align:center;color:#6c757d">No hay ventas para los filtros seleccionados.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
