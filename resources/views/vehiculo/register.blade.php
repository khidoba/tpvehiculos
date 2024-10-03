@extends('adminlte::page')

@section('preloader')
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Cargando...</h4>
@stop

@section('title', 'Registro')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
@endsection

@section('content_header')
    <h1>Registrar - Sistema Mantenimiento</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="card card-success col-sm-5 mb-5 mb-sm-5">
                <div class="card-header">
                    <h3 class="card-title">Datos vehículo y refrigeración</h3>
                </div>
                <form action="{{ route('registrar') }}" method="POST" id='vehiculoForm' novalidate>
                    @csrf
                    <div class="card-body">
                        <div class="h5 pb-2 mb-4 text-success border-bottom border-success">
                          Vehículo
                        </div>

                        <div class="form-group">
                            <label for="agencia">Agencia:</label>
                            <select class="form-control" id="agencia" name="agencia" >
                                <option value="" selected>Por favor selecciona...</option>
                                <option value="Aguadulce">Aguadulce</option>
                                <option value="Boqueron">Boqueron</option>
                                <option value="La Chorrera">La Chorrera</option>
                                <option value="Tocumen">Tocumen</option>
                                <option value="Panama">Panamá</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="camion">Vehiculo:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                </div>
                                <input type="test" class="form-control" id="vehiculo" name="vehiculo" placeholder="Digite el número de camión, ej: 1234" value="{{ old('vehiculo') }}" >
                            </div>
                            @error('vehiculo')
                                <p class="h6 text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="placa">Placa:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="text" class="form-control" id="placa" name="placa" placeholder="Digite la placa, ej: 582710" value="{{ old('placa') }}" >
                            </div>
                            @error('placa')
                                <p class="h6 text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="marca">Marca:</label>
                            <select class="form-control" id="marca" name="marca" >
                                <option value="" selected>Por favor selecciona...</option>
                                <option value="Freightliner">Freightliner</option>
                                <option value="Isuzu">Isuzu</option>
                                <option value="Hino">Hino</option>
                                <option value="International">International</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="modelo">Modelo:</label>
                            <select class="form-control" id="modelo" name="modelo" >
                                <option value="" selected>Por favor selecciona...</option>
                                <option value="M2">M2</option>
                                <option value="NQR71K-22">NQR71K-22</option>
                                <option value="FC3">FC3</option>
                                <option value="FRR">FRR</option>
                                <option value="FSR">FSR</option>
                                <option value="4900">4900</option>
                                <option value="FRR33L-02">FRR33L-02</option>
                                <option value="4400">4400</option>
                                <option value="4300">4300</option>
                                <option value="SERIE 500">Serie 500</option>
                                <option value="F8100">F8100</option>
                                <option value="4HF1">4HF1</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="anio">Año:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="text" class="form-control" id="anio" name="anio" placeholder="Digite el año, ej: 1981" value="{{ old('anio') }}" >
                            </div>
                            @error('anio')
                                <p class="h6 text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dimension_pies">Dimensión en pies:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-expand"></i></span>
                                </div>
                                <input type="text" step="0.01" class="form-control" id="dimension_pies" name="dimension_pies" placeholder="Digite pies, ej: 24" value="{{ old('dimension_pies') }}" >
                            </div>
                            @error('dimension_pies')
                                <p class="h6 text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="h5 pb-2 mb-4 text-success border-bottom border-success">
                          Refrigeración
                        </div>

                        <div class="form-group">
                            <label for="tipo_temperatura">Temperatura:</label>
                            <select class="form-control" id="tipo_temperatura" name="tipo_temperatura">
                                <option value="" selected>Por favor selecciona...</option>
                                <option value="Mixto">Mixto</option>
                                <option value="Mixto 3 Temp">Mixto 3 Temp</option>
                                <option value="Seco">Seco</option>
                                <option value="Seco (Merma)">Seco (Merma)</option>
                                <option value="Refrigerado">Refrigerado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tipo_refrigeracion">Refrigeración:</label>
                            <select class="form-control" id="tipo_refrigeracion" name="tipo_refrigeracion">
                                <option value="" selected>Por favor selecciona...</option>
                                <option value="T1000S">T1000S</option>
                                <option value="T1000R">T1000R</option>
                                <option value="SUPRA 950 MT">Supra 950 MT</option>
                                <option value="FREIGHTLINER">Freightliner</option>
                                <option value="HINO">Hino</option>
                                <option value="INTERNATIONAL">International</option>
                                <option value="ISUZU">Isuzu</option>
                                <option value="T1080R">T1080R</option>
                                <option value="T600R">T600R</option>
                                <option value="T680R">T680R</option>
                            </select>
                        </div>

                        <hr>

                        <div class="h5 pb-2 mb-4 text-success border-bottom border-success">
                          Estado sistema
                        </div>


                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="" selected>Por favor selecciona...</option>
                                <option value="Activo">Activo</option>
                                <option value="En mantenimiento">En mantenimiento</option>
                                <option value="Fuera de servicio">Fuera de servicio</option>
                            </select>
                        </div>

                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-check"></i> Guardar</button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger btn-block"><i class="fa fa-ban"></i> Cancelar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="message-container" class="mt-3"></div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#vehiculoForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).attr('method'),
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Registro exitoso',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#vehiculoForm')[0].reset();
                            }
                        });
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '<ul>';
                        $.each(errors, function(key, value) {
                            errorMessage += '<li>' + value + '</li>';
                        });
                        errorMessage += '</ul>';

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@stop
