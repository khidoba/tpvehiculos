@extends('adminlte::page')

@section('title', 'Editar Vehículo')

@section('content_header')
    <h1>Editar Vehículo</h1>
@stop

@section('content')
    <div class="card card-success col-sm-5 mb-5 mb-sm-5">
        <div class="card-body">
            <form id="editForm" action="{{ route('vehiculos.update', $vehiculo->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="agencia">Agencia:</label>
                    <input type="text" class="form-control" id="agencia" name="agencia" value="{{ $vehiculo->agencia }}" required>
                </div>

                <div class="form-group">
                    <label for="vehiculo">Vehículo:</label>
                    <input type="text" class="form-control" id="vehiculo" name="vehiculo" value="{{ $vehiculo->vehiculo }}" required>
                </div>

                <div class="form-group">
                    <label for="placa">Placa:</label>
                    <input type="text" class="form-control" id="placa" name="placa" value="{{ $vehiculo->placa }}" required>
                </div>

                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <select class="form-control" id="marca" name="marca" required>
                        <option value="">Seleccione una marca</option>
                        <option value="Freightliner" {{ $vehiculo->marca == 'Freightliner' ? 'selected' : '' }}>Freightliner</option>
                        <option value="Isuzu" {{ $vehiculo->marca == 'Isuzu' ? 'selected' : '' }}>Isuzu</option>
                        <option value="Hino" {{ $vehiculo->marca == 'Hino' ? 'selected' : '' }}>Hino</option>
                        <option value="International" {{ $vehiculo->marca == 'International' ? 'selected' : '' }}>International</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <select class="form-control" id="modelo" name="modelo" required>
                        <option value="">Seleccione un modelo</option>
                        <!-- Las opciones se llenarán dinámicamente con JavaScript -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="anio">Año:</label>
                    <input type="number" class="form-control" id="anio" name="anio" value="{{ $vehiculo->anio }}" required min="1900" max="{{ date('Y') + 1 }}">
                </div>

                <div class="form-group">
                    <label for="dimension_pies">Dimensión en pies:</label>
                    <input type="number" class="form-control" id="dimension_pies" name="dimension_pies" value="{{ $vehiculo->dimension_pies }}" required step="0.01">
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="">Seleccione un estado</option>
                        <option value="Activo" {{ $vehiculo->estado == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="En mantenimiento" {{ $vehiculo->estado == 'En mantenimiento' ? 'selected' : '' }}>En mantenimiento</option>
                        <option value="Fuera de servicio" {{ $vehiculo->estado == 'Fuera de servicio' ? 'selected' : '' }}>Fuera de servicio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo_temperatura">Tipo de Temperatura:</label>
                    <select class="form-control" id="tipo_temperatura" name="tipo_temperatura">
                        <option value="">Seleccione un tipo de temperatura</option>
                        <option value="Mixto" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_temperatura == 'Mixto' ? 'selected' : '' }}>Mixto</option>
                        <option value="Mixto 3 Temp" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_temperatura == 'Mixto 3 Temp' ? 'selected' : '' }}>Mixto 3 Temp</option>
                        <option value="Seco" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_temperatura == 'Seco' ? 'selected' : '' }}>Seco</option>
                        <option value="Seco (Merma)" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_temperatura == 'Seco (Merma)' ? 'selected' : '' }}>Seco (Merma)</option>
                        <option value="Refrigerado" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_temperatura == 'Refrigerado' ? 'selected' : '' }}>Refrigerado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo_refrigeracion">Tipo de Refrigeración:</label>
                    <select class="form-control" id="tipo_refrigeracion" name="tipo_refrigeracion">
                        <option value="">Seleccione un tipo de refrigeración</option>
                        <option value="T1000S" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_refrigeracion == 'T1000S' ? 'selected' : '' }}>T1000S</option>
                        <option value="T1000R" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_refrigeracion == 'T1000R' ? 'selected' : '' }}>T1000R</option>
                        <option value="SUPRA 950 MT" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_refrigeracion == 'SUPRA 950 MT' ? 'selected' : '' }}>SUPRA 950 MT</option>
                        <option value="T1080R" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_refrigeracion == 'T1080R' ? 'selected' : '' }}>T1080R</option>
                        <option value="T600R" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_refrigeracion == 'T600R' ? 'selected' : '' }}>T600R</option>
                        <option value="T680R" {{ optional($vehiculo->sistemaRefrigeracion)->tipo_refrigeracion == 'T680R' ? 'selected' : '' }}>T680R</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Vehículo</button>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            var modelosPorMarca = {
                'Freightliner': ['M2'],
                'Isuzu': ['NQR71K-22'],
                'Hino': ['FC3', 'FRR', 'FSR'],
                'International': ['4900', '4400', '4300']
            };

            function actualizarModelos() {
                var marcaSeleccionada = $('#marca').val();
                var modeloSelect = $('#modelo');
                var modeloActual = '{{ $vehiculo->modelo }}';

                modeloSelect.empty();
                modeloSelect.append('<option value="">Seleccione un modelo</option>');

                if (marcaSeleccionada && modelosPorMarca[marcaSeleccionada]) {
                    $.each(modelosPorMarca[marcaSeleccionada], function(i, modelo) {
                        modeloSelect.append($('<option></option>')
                            .attr('value', modelo)
                            .text(modelo)
                            .prop('selected', modelo === modeloActual)
                        );
                    });
                }
            }

            $('#marca').change(actualizarModelos);
            actualizarModelos(); // Ejecutar al cargar la página

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '{{ route("vehiculos.index") }}';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Hubo un problema al actualizar el vehículo.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud:', status, error);
                        console.log('Respuesta del servidor:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la solicitud.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@stop
