@extends('template.administrador')

@section('title', 'CATEGORIAS')
@section('name-section', 'CATEGORIAS')

@section('description', 'Nuevo registro de categorias')
@yield('item-breadcrumd', 'Nuevo')
@yield('section-breadcrumd', 'Categorias')
@yield('module-breadcrumd', 'Configuraciones')


@section('content')
   <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary"> 
                <div class="card-body">
                <form action="{{route('categories.store') }}" method="POST">
                    @csrf
                    @include('settings.categories._form')
                </div>
            </div>

@endsection

