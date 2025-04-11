{{-- resources/views/clientes/crear.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-user-plus me-2"></i>Registrar Cliente
    </h4>

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        @include('clientes.partials.form', ['formType' => 'create'])

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cliente
            </button>
        </div>
    </form>
</div>

<script>
    function setLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    document.getElementById("ubicacion").value = `https://www.google.com/maps?q=${latitude},${longitude}`;
                },
                function(error) {
                    alert('Error al obtener la ubicación.');
                }
            );
        } else {
            alert('Tu navegador no admite geolocalización.');
        }
    }
</script>
@endsection
