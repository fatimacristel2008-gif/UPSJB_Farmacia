@extends('template.administrador')

@section('title', 'MEDICAMENTOS')
@section('name-section', 'MEDICAMENTOS')

@section('description', 'Actualizar registro de medicamento')
@section('item-breadcrumb', 'Actualizar')
@section('section-breadcrumb', 'Medicamentos')
@section('module-breadcrumb', 'Gestión')


@section('content')
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <form action="{{ route('medicines.update', $medicine) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('management.medicines.form')
        </form>
    </div>
</div>
@endsection
