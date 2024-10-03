@extends('adminlte::page')

@section('title', 'Asignar Vehículos')

@section('content_header')
    <h1>Asignar Vehículos a Técnicos</h1>
@stop

@section('content')
    <div class="card card-success col-sm-5 mb-5 mb-sm-5">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('vehiculos.asignar') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="tecnico_id">Técnico</label>
                    <select name="tecnico_id" id="tecnico_id" class="form-control" required>
                        <option value="">Seleccione un técnico</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="vehiculo_ids">Vehículos</label>
                    <select name="vehiculo_ids[]" id="vehiculo_ids" class="form-control" multiple required>
                        <!-- Las opciones se cargarán dinámicamente con Select2 -->
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Asignar Vehículos</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Vehículos Asignados</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="vehiculos-asignados-table">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Año</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los vehículos asignados se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    @parent
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let dataTable;

            $('#vehiculo_ids').select2({
                placeholder: 'Seleccione uno o varios vehículos',
                allowClear: true,
                ajax: {
                    url: '{{ route("get.vehiculos") }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.placa + ' - ' + item.modelo + ' (' + item.agencia + ')',
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#tecnico_id').change(function() {
                var tecnicoId = $(this).val();
                if(tecnicoId) {
                    $.ajax({
                        url: '{{ route("get.vehiculos.asignados") }}',
                        data: {tecnico_id: tecnicoId},
                        success: function(data) {
                            if(dataTable) {
                                dataTable.destroy();
                            }

                            var tableBody = $('#vehiculos-asignados-table tbody');
                            tableBody.empty();

                            $.each(data, function(index, vehiculo) {
                                tableBody.append(`
                                    <tr>
                                        <td>${vehiculo.placa}</td>
                                        <td>${vehiculo.modelo}</td>
                                        <td>${vehiculo.marca}</td>
                                        <td>${vehiculo.anio}</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger desasignar-btn"
                                                    data-vehiculo-id="${vehiculo.id}"
                                                    data-tecnico-id="${tecnicoId}">
                                                <i class="fas fa-unlink"></i> Desasignar
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            });

                            dataTable = $('#vehiculos-asignados-table').DataTable({
                                "language": {
                                    "url": "https://cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json"
                                },
                                "pageLength": 5,
                                "order": [[0, "asc"]]
                            });
                        }
                    });
                } else {
                    if(dataTable) {
                        dataTable.clear().draw();
                    }
                }
            });

            $(document).on('click', '.desasignar-btn', function() {
                var vehiculoId = $(this).data('vehiculo-id');
                var tecnicoId = $(this).data('tecnico-id');
                var row = $(this).closest('tr');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¿Quieres desasignar este vehículo?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, desasignar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("vehiculos.desasignar") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                vehiculo_id: vehiculoId,
                                tecnico_id: tecnicoId
                            },
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire(
                                        'Desasignado!',
                                        response.message,
                                        'success'
                                    );
                                    dataTable.row(row).remove().draw();
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function() {
                                Swal.fire(
                                    'Error!',
                                    'Hubo un problema al desasignar el vehículo.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
