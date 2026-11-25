@extends('template.administrador')

@section('title', 'DASHBOARD')
@section('name-section', 'DASHBOARD')

@section('description', 'Resumen del sistema de venta de medicamentos')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Dashboard')
@section('module-breadcrumb', 'Principal')


@section('content')
<div class="row g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('medicines.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-box-seam fs-2 text-primary"></i>
                <div>
                    <div class="text-secondary small">Medicamentos</div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalMedicines }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('customers.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-people fs-2 text-primary"></i>
                <div>
                    <div class="text-secondary small">Clientes</div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalCustomers }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('sales.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-cart3 fs-2 text-primary"></i>
                <div>
                    <div class="text-secondary small">Ventas registradas</div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalSales }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                <div>
                    <div class="text-secondary small">Stock bajo</div>
                    <div class="fs-4 fw-bold text-dark">{{ $lowStock }}</div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
