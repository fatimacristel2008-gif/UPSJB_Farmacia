@extends('template.administrador')

@section('title', 'VENTAS')
@section('name-section', 'VENTAS')
@section('description', 'Editar venta')
@section('item-breadcrumb', 'Editar')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Gestión')

@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('sales.update', $sale) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Cliente</label>
                    <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $sale->customer_id) == $customer->id)>{{ $customer->name }} - DNI {{ $customer->dni }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label for="user_id" class="form-label">Vendedor</label>
                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id', $sale->user_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="text-uppercase small text-muted mt-2 mb-2">Medicamentos</h6>

            @if ($errors->has('items') || $errors->has('items.*'))
                <div class="alert alert-danger py-2">
                    @foreach (collect($errors->get('items*'))->flatten()->unique() as $message)<div>{{ $message }}</div>@endforeach
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th class="small text-uppercase">Medicamento</th><th class="small text-uppercase text-center" style="width:120px">Cantidad</th><th class="small text-uppercase text-end" style="width:120px">Precio histórico</th><th class="small text-uppercase text-end" style="width:120px">Subtotal</th><th style="width:50px"></th></tr></thead>
                    <tbody id="items">
                        @php
                            $oldItems = old('items');
                            $items = $oldItems ?? $sale->details->map(fn ($detail) => ['medicine_id' => $detail->medicine_id, 'amount' => $detail->amount])->all();
                        @endphp
                        @foreach ($items as $i => $item)
                            @include('management.sales.item-row', ['index' => $i, 'item' => $item])
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="3" class="text-end fw-semibold">Total (incluye IGV)</td><td class="text-end fw-bold" id="total">S/ 0.00</td><td></td></tr></tfoot>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-outline-primary btn-sm mb-4"><i class="bi bi-plus-lg me-1"></i>Agregar medicamento</button>
            <div><button type="submit" class="btn btn-primary text-white">Guardar cambios</button><a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">Cancelar</a></div>
        </form>
    </div>
</div>

<template id="item-template">@include('management.sales.item-row', ['index' => '__INDEX__', 'item' => []])</template>
@endsection

@push('scripts')
<script>
    const tbody = document.getElementById('items');
    const template = document.getElementById('item-template').innerHTML;
    let index = {{ count($items) ? max(array_keys($items)) + 1 : 0 }};

    function recalculate() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            const option = row.querySelector('select').selectedOptions[0];
            const medicineId = option?.value;
            let price = parseFloat(option?.dataset.price || 0);
            @foreach ($sale->details as $detail)
                if (String(medicineId) === '{{ $detail->medicine_id }}') price = {{ (float) $detail->price_unit }};
            @endforeach
            const amount = parseInt(row.querySelector('input').value || 0);
            const subtotal = price * amount;
            row.querySelector('.price').textContent = 'S/ ' + price.toFixed(2);
            row.querySelector('.subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
            total += subtotal;
        });
        document.getElementById('total').textContent = 'S/ ' + total.toFixed(2);
    }

    document.getElementById('add-item').addEventListener('click', () => { tbody.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index++)); recalculate(); });
    tbody.addEventListener('click', e => { if (e.target.closest('.remove-item')) { e.target.closest('tr').remove(); recalculate(); } });
    tbody.addEventListener('input', recalculate);
    tbody.addEventListener('change', recalculate);
    recalculate();
</script>
@endpush
