@extends('template.administrador')

@section('title', 'REPORTES')
@section('name-section', 'REPORTES')

@section('description', 'Reporte de ventas y stock')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Reportes')
@section('module-breadcrumb', 'Principal')


@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary mb-3">
    <div class="card-body">
        <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label for="from" class="form-label small">Desde</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-md-4">
                <label for="to" class="form-label small">Hasta</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-md-4 d-flex gap-1">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i>Consultar</button>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary w-100" title="Mes actual"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary small">Ventas en el periodo</div>
                <div class="fs-4 fw-bold">{{ $sales->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary small">Total vendido (incluye IGV)</div>
                <div class="fs-4 fw-bold">S/ {{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary small">IGV (18%)</div>
                <div class="fs-4 fw-bold">S/ {{ number_format($totalIgv, 2) }}</div>
                <div class="text-secondary small">Op. gravada: S/ {{ number_format($totalSales - $totalIgv, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="card bg-white border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase small text-muted mb-3">Medicamentos más vendidos</h6>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase text-center">Nº</th>
                            <th class="small text-uppercase">Medicamento</th>
                            <th class="small text-uppercase text-center">Cantidad</th>
                            <th class="small text-uppercase text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topMedicines as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->medicine->name }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">S/ {{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No hay ventas en el periodo</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card bg-white border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase small text-muted mb-3">Medicamentos con stock bajo</h6>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="small text-uppercase">Medicamento</th>
                            <th class="small text-uppercase">Categoría</th>
                            <th class="small text-uppercase text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lowStockMedicines as $medicine)
                            <tr>
                                <td>{{ $medicine->name }}</td>
                                <td>{{ $medicine->category->name }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $medicine->stock }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No hay medicamentos con stock bajo</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <h6 class="text-uppercase small text-muted mb-3">Ventas del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</h6>
        <table class="table table-sm table-hover w-100 bg-white mb-0">
            <thead>
                <tr>
                    <th class="text-center align-middle text-uppercase small">Nº</th>
                    <th class="text-center align-middle text-uppercase small">Fecha</th>
                    <th class="text-center align-middle text-uppercase small">Cliente</th>
                    <th class="text-center align-middle text-uppercase small">Vendedor</th>
                    <th class="text-center align-middle text-uppercase small">IGV</th>
                    <th class="text-center align-middle text-uppercase small">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td class="text-center align-middle">
                            <a href="{{ route('sales.show', $sale) }}">{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</a>
                        </td>
                        <td class="text-center align-middle">{{ $sale->date->format('d/m/Y H:i') }}</td>
                        <td class="text-start align-middle">{{ $sale->customer->name }}</td>
                        <td class="text-start align-middle">{{ $sale->user->name }}</td>
                        <td class="text-end align-middle">S/ {{ number_format($sale->igv, 2) }}</td>
                        <td class="text-end align-middle">S/ {{ number_format($sale->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No hay ventas en el periodo</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
