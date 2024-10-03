@extends('adminlte::page')

@section('preloader')
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Cargando...</h4>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
@stop

@section('title', 'Registro')

@section('content_header')
    <h1>Vehiculos - Sistema Mantenimiento</h1>
@stop

@section('content')
    <h6>Listado de vehiculos</h6>

    <div class="card">
        <div class="card-body">
            @if(auth()->user()->hasRole('Administrador de Sistema'))
                <h3>Todos los vehículos</h3>
            @else
                <h3>Vehículos de {{ auth()->user()->agencia }}</h3>
            @endif
            <div class="table-responsive">
                <table class="table table-striped" id="vehiculos">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Agencia</th>
                            <th>Vehiculo</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Dimension</th>
                            <th>Temperatura</th>
                            <th>Refrigeración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vehiculos as $vehiculo)
                            <tr>
                                <td>
                                    @if($vehiculo->estado == 'Vehiculo Preventivo' || $vehiculo->estado == 'Vehiculo Correctivo'
                                        || $vehiculo->estado == 'Refrigeracion Preventivo' || $vehiculo->estado == 'Refrigeracion Correctivo')
                                        <span class="badge bg-warning text-dark">{{ $vehiculo->estado }}</span>
                                    @else
                                        @if($vehiculo->estado == 'Activo')
                                            <span class="badge bg-success text-dark">{{ $vehiculo->estado }}</span>
                                        @else
                                            <span class="badge bg-danger text-dark">{{ $vehiculo->estado }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $vehiculo->agencia }}</td>
                                <td>{{ $vehiculo->vehiculo }}</td>
                                <td>{{ $vehiculo->placa }}</td>
                                <td>{{ $vehiculo->marca }}</td>
                                <td>{{ $vehiculo->modelo }}</td>
                                <td>{{ $vehiculo->dimension_pies }}</td>
                                <td>{{ $vehiculo->sistemarefrigeracion->tipo_temperatura ?? 'N/A' }}</td>
                                <td>{{ $vehiculo->sistemarefrigeracion->tipo_refrigeracion ?? 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-success edit-btn" data-id="{{ $vehiculo->id }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $vehiculo->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning calendar-btn" data-id="{{ $vehiculo->id }}">
                                        <i class="fas fa-calendar-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info report-btn" data-id="{{ $vehiculo->id }}">
                                        <i class="fas fa-glasses"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mantenimientoModal" tabindex="-1" aria-labelledby="mantenimientoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mantenimientoModalLabel">Agendar Mantenimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="mantenimientoForm">
                        @csrf
                        <input type="hidden" id="vehiculo_id" name="vehiculo_id">
                        <div class="mb-3">
                            <label for="vehiculo" class="form-label">Vehículo</label>
                            <input type="text" class="form-control" id="vehiculo" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="tipoMantenimiento" class="form-label">Tipo de Mantenimiento</label>
                            <select class="form-select" id="tipoMantenimiento" name="tipoMantenimiento" required>
                                <option value="Preventivo">Preventivo</option>
                                <option value="Correctivo">Correctivo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="actividad" class="form-label">Actividad a realizar:</label>
                            <textarea class="form-control" id="actividad" name="actividad" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tipoComponente" class="form-label">Componente a mantener</label>
                            <select class="form-select" id="tipoComponente" name="tipoComponente" required>
                                <option value="vehiculo">Vehículo</option>
                                <option value="sistemaRefrigeracion">Sistema de Refrigeración</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fechaInicial" class="form-label">Fecha Inicial</label>
                            <input type="date" class="form-control" id="fechaInicial" name="fechaInicial" required>
                        </div>
                        <div class="mb-3">
                            <label for="fechaFinal" class="form-label">Fecha Final</label>
                            <input type="date" class="form-control" id="fechaFinal" name="fechaFinal" required>
                        </div>

                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Técnico</label>
                            <select class="form-select" id="usuario_id" name="usuario_id" required>
                                <option value="">Seleccione un técnico</option>
                            </select>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="guardarMantenimiento">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Reporte de Mantenimientos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="vehiculoInfo"></h6>
                    <hr>
                    <h6>Mantenimientos Realizados</h6>
                    <table class="table table-striped" id="mantenimientosRealizadosTable">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Técnico</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <h6>Mantenimientos Programados</h6>
                    <table class="table table-striped" id="mantenimientosProgramadosTable">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Técnico</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detallesMantenimientoModal" tabindex="-1" aria-labelledby="detallesMantenimientoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallesMantenimientoModalLabel">Detalles del Mantenimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Información General</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Componente</th>
                                    <td id="componenteMantenimiento"></td>
                                </tr>
                                <tr>
                                    <th>Fecha inicial</th>
                                    <td id="fechaInicialMantenimiento"></td>
                                </tr>
                                <tr>
                                    <th>Fecha final</th>
                                    <td id="fechaFinalMantenimiento"></td>
                                </tr>
                            </table>
                            <h6>Materiales utilizados</h6>
                            <table class="table table-bordered" id="materialesMantenimiento">
                                <!-- Se llenará dinámicamente -->
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6>Diagnóstico y acciones</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Diagnósticos</th>
                                    <td id="diagnosticosMantenimiento"></td>
                                </tr>
                                <tr>
                                    <th>Acciones</th>
                                    <td id="accionesMantenimiento"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6>Evidencia</h6>
                            <div id="evidenciaMantenimiento">
                                <!-- Se llenará dinámicamente con enlaces a las imágenes -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fasesModal" tabindex="-1" aria-labelledby="fasesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fasesModalLabel">Detalles de las Fases</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="fasesTable">
                        <thead>
                            <tr>
                                <th>Tipo de Fase</th>
                                <th>Fecha de Registro</th>
                                <th>Detalles de fase</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="detallesFaseModal" tabindex="-1" aria-labelledby="detallesFaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesFaseModalLabel">Detalles de la Fase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Descripción</h6>
                <p id="descripcionFase"></p>
                <h6>Acciones Realizadas</h6>
                <p id="accionesFase"></p>
                <h6>Observaciones</h6>
                <p id="observacionesFase"></p>
                <h6>Materiales</h6>
                <p id="materialesFase"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

    <!-- Modal para Descripción -->
    <div class="modal fade" id="descripcionModal" tabindex="-1" aria-labelledby="descripcionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="descripcionModalLabel">Descripción</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="descripcionModalBody">
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Acciones Realizadas -->
    <div class="modal fade" id="accionesModal" tabindex="-1" aria-labelledby="accionesModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="accionesModalLabel">Acciones Realizadas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="accionesModalBody">
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Observaciones -->
    <div class="modal fade" id="observacionesModal" tabindex="-1" aria-labelledby="observacionesModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="observacionesModalLabel">Observaciones</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="observacionesModalBody">
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Materiales -->
    <div class="modal fade" id="materialesModal" tabindex="-1" aria-labelledby="materialesModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="materialesModalLabel">Materiales</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="materialesModalBody">
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Foto -->
    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="fotoModalLabel">Foto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="fotoModalBody">
          </div>
        </div>
      </div>
    </div>

@stop

@section('css')
    @parent
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .maintenance-details-modal .swal2-html-container {
            max-height: 80vh;
            overflow-y: auto;
        }

        .maintenance-details .timeline {
            position: relative;
            padding: 20px 0;
        }

        .maintenance-details .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50px;
            height: 100%;
            width: 2px;
            background: #e5e5e5;
        }

        .maintenance-details .timeline-item {
            margin-bottom: 30px;
            position: relative;
        }

        .maintenance-details .timeline-icon {
            position: absolute;
            left: 40px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            color: white;
            font-size: 14px;
        }

        .maintenance-details .timeline-content {
            margin-left: 80px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
        }

        .maintenance-details .timeline-content h5 {
            margin-top: 0;
            color: #333;
        }

        .maintenance-details img {
            max-width: 100%;
            border-radius: 4px;
        }

        #detallesMantenimientoModal .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }

        #detallesMantenimientoModal .table {
            font-size: 0.9rem;
        }

        .evidencia-link {
            color: #007bff;
            text-decoration: none;
        }

        .evidencia-link:hover {
            text-decoration: underline;
        }

        .swal2-popup .swal2-image {
            max-width: 100%;
            height: auto;
        }
    </style>
@endsection


@section('js')

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.datatables.net/buttons/3.1.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.colVis.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script>
        let dataTable;
        var mantenimientosData = {
            realizados: [],
            programados: []
        };

        $(document).ready(function() {
            dattable = $('#vehiculos').DataTable({
                responsive: true,
                autoWidth: true,

                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json"
                },
                "pageLength": 10,

                'dom': 'Blfrtip',

                'buttons': [
                    {
                        "extend": "copyHtml5",
                        "text": "<i class='far fa-copy'></i>",
                        "titleAttr":"Copiar",
                        "className": "btn btn-secondary"
                    },{
                        "extend": "excelHtml5",
                        "text": "<i class='fas fa-file-excel'></i>",
                        "titleAttr":"Esportar a Excel",
                        "className": "btn btn-success"
                    },{
                        "extend": "pdfHtml5",
                        "text": "<i class='fas fa-file-pdf'></i>",
                        "titleAttr":"Esportar a PDF",
                        "className": "btn btn-danger"
                    },{
                        "extend": "csvHtml5",
                        "text": "<i class='fas fa-file-csv'></i>/",
                        "titleAttr":"Esportar a CSV",
                        "className": "btn btn-info"
                    }
                ],

                order: [[0, 'desc']],
                @if(!auth()->user()->hasRole('Administrador de Sistema'))
                columnDefs: [
                    {
                        targets: [1], // Índice de la columna 'Agencia'
                        visible: false
                    }
                ],
                @endif
            });

            // Usar delegación de eventos para los botones de editar y eliminar
            $('#vehiculos tbody').on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                // Redirigir a la página de edición
                window.location.href = '/vehiculos/' + id + '/edit';
            });

            $('#vehiculos tbody').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                var row = $(this).closest('tr');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "El vehículo será marcado como eliminado, pero los mantenimientos asociados se mantendrán.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/vehiculos/' + id,
                            type: 'DELETE',
                            data: {
                                "_token": $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    dattable.row(row).remove().draw(false);
                                    Swal.fire(
                                        'Eliminado!',
                                        'El vehículo ha sido marcado como eliminado.',
                                        'success'
                                    );
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Hubo un problema al eliminar el vehículo.', 'error');
                            }
                        });
                    }
                });
            });

            $('#vehiculos tbody').on('click', '.calendar-btn', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '/vehiculos/' + id + '/info',
                    type: 'GET',
                    success: function(response) {
                        $('#vehiculo_id').val(response.id);
                        $('#vehiculo').val(response.vehiculo + ' - Placa: ' + response.placa + ' - Marca:' + response.marca + ' - Modelo: ' + response.modelo);

                        var today = new Date().toISOString().split('T')[0];
                        $('#fechaInicial').val('').attr('min', today);
                        $('#fechaFinal').val('').attr('min', today);
                        $('#tipoComponente').val('');
                        $('#usuario_id').empty().append('<option value="">Seleccione un técnico</option>');

                        $('#mantenimientoModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'No se pudo obtener la información del vehículo.', 'error');
                    }
                });
            });

            $('#vehiculos tbody').on('click', '.report-btn', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '/vehiculos/' + id + '/mantenimiento-report',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            $('#vehiculoInfo').text('Vehículo: ' + data.vehiculo + ' - Placa: ' + data.placa);

                            // Almacenar los datos recibidos
                            mantenimientosData.realizados = data.mantenimientosRealizados;
                            mantenimientosData.programados = data.mantenimientosProgramados;

                            // Limpiar y llenar la tabla de mantenimientos realizados
                            var realizadosBody = $('#mantenimientosRealizadosTable tbody');
                            realizadosBody.empty();
                            mantenimientosData.realizados.forEach(function(m) {
                                realizadosBody.append(`
                                    <tr data-mantenimiento-id="${m.id}">
                                        <td>${m.tipo}</td>
                                        <td>${m.fechaFinal}</td>
                                        <td>${m.tecnico}</td>
                                        <td><button class="btn btn-sm btn-info ver-detalles">Ver detalles</button></td>
                                    </tr>
                                `);
                            });

                            // Limpiar y llenar la tabla de mantenimientos programados
                            var programadosBody = $('#mantenimientosProgramadosTable tbody');
                            programadosBody.empty();
                            mantenimientosData.programados.forEach(function(m) {
                                programadosBody.append(`
                                    <tr data-mantenimiento-id="${m.id}">
                                        <td>${m.tipo}</td>
                                        <td>${m.fechaFinal}</td>
                                        <td>${m.tecnico}</td>
                                        <td><button class="btn btn-sm btn-info ver-detalles">Ver detalles</button></td>
                                    </tr>
                                `);
                            });

                            $('#reportModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo obtener el reporte de mantenimientos', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', status, error);
                        Swal.fire('Error', 'No se pudo obtener el reporte de mantenimientos', 'error');
                    }
                });
            });

            // Función para formatear fecha y hora
            function formatDateTime(dateTimeString) {
                return moment(dateTimeString).format('YYYY-MM-DD - HH:mm');
            }

$('#reportModal').on('click', '.ver-detalles', function() {
    var mantenimientoId = $(this).closest('tr').data('mantenimiento-id');
    var mantenimiento = findMantenimientoById(mantenimientoId);

    if (mantenimiento) {
        var fasesTableBody = $('#fasesTable tbody');
        fasesTableBody.empty();

        var fasesUnicas = new Set(mantenimiento.fases.map(JSON.stringify));

        Array.from(fasesUnicas).map(JSON.parse).forEach(function(fase) {
            fasesTableBody.append(`
                <tr>
                    <td>${fase.tipo_fase}</td>
                    <td>${formatDateTime(fase.fecha_hora)}</td>
                    <td>
                        <a href="#" class="detalle-fase-link" data-fase='${encodeURIComponent(JSON.stringify(fase))}'>
                            <i class="fa-solid fa-info"></i>
                        </a>
                    </td>
                    <td>${fase.foto_url ? `<a href="#" class="detalle-link" data-tipo="foto" data-contenido="${encodeURIComponent(fase.foto_url)}"><i class="fa-regular fa-camera-retro"></i></a>` : 'N/A'}</td>
                </tr>
            `);
        });

        $('#reportModal').modal('hide');
        $('#fasesModal').modal('show');
    }
});

$(document).on('click', '.detalle-fase-link', function(e) {
    e.preventDefault();
    try {
        var faseString = decodeURIComponent($(this).data('fase'));
        var fase = JSON.parse(faseString);

        $('#descripcionFase').text(fase.descripcion);
        $('#accionesFase').text(fase.acciones_realizadas);
        $('#observacionesFase').text(fase.observaciones || 'N/A');
        $('#materialesFase').html(fase.materiales.map(m => `${m.nombre}: ${m.cantidad}`).join('<br>'));

        $('#fasesModal').modal('hide');
        $('#detallesFaseModal').modal('show');
    } catch (error) {
        console.error('Error al parsear los datos de la fase:', error);
        alert('Hubo un error al cargar los detalles de la fase. Por favor, inténtelo de nuevo.');
    }
});

$('#detallesFaseModal').on('hidden.bs.modal', function () {
    $('#fasesModal').modal('show');
});

            $(document).on('click', '.detalle-link', function(e) {
                e.preventDefault();
                var tipo = $(this).data('tipo');
                var contenido = decodeURIComponent($(this).data('contenido'));

                switch(tipo) {
                    case 'descripcion':
                    case 'acciones':
                    case 'observaciones':
                    case 'materiales':
                        $(`#${tipo}ModalBody`).html(contenido);
                        $(`#${tipo}Modal`).modal('show');
                        break;
                    case 'foto':
                        $('#fotoModalBody').html(`<img src="${contenido}" class="img-fluid" alt="Foto de evidencia">`);
                        $('#fotoModal').modal('show');
                        break;
                }
            });

            // Añadir un nuevo manejador de eventos para los enlaces de evidencia
            $(document).on('click', '.evidencia-link', function(e) {
                e.preventDefault();
                var imageSrc = $(this).data('src');
                Swal.fire({
                    imageUrl: imageSrc,
                    imageAlt: 'Evidencia',
                    width: 'auto',
                    customClass: {
                        image: 'img-fluid'
                    }
                });
            });

            // Función para abrir la imagen en un modal
            function openImageModal(src) {
                Swal.fire({
                    imageUrl: src,
                    imageAlt: 'Foto de evidencia',
                    width: 'auto',
                    customClass: {
                        image: 'img-fluid'
                    }
                });
            }

            $('#fechaInicial').change(function() {
                $('#fechaFinal').attr('min', $(this).val());
            });

            $('#fechaInicial, #fechaFinal, #tipoComponente').change(function() {
                var vehiculoId = $('#vehiculo_id').val();
                var tipoComponente = $('#tipoComponente').val();
                var fechaInicial = $('#fechaInicial').val();
                var fechaFinal = $('#fechaFinal').val();

                if (vehiculoId && tipoComponente && fechaInicial && fechaFinal) {
                    cargarTecnicosAsignados(vehiculoId, tipoComponente);
                }
            });

            $('#guardarMantenimiento').click(function() {
                // Obtener los valores de fecha desde los inputs
                var fechaInicial = $('#fechaInicial').val(); // La fecha viene en formato 'YYYY-MM-DD'
                var fechaFinal = $('#fechaFinal').val(); // La fecha viene en formato 'YYYY-MM-DD'

                // Obtener la fecha actual en la zona horaria local sin conversión a UTC
                var today = new Date();
                var diaHoy = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2);

                console.log('fechaInicial', fechaInicial);
                console.log('fechaFinal', fechaFinal);
                console.log('today', diaHoy);

                // Validar que la fecha inicial no sea anterior a hoy
                if (fechaInicial < diaHoy) {
                    Swal.fire('Error!', 'No se pueden agendar mantenimientos en fechas pasadas.', 'error');
                    return;
                }

                // Validar que la fecha final no sea anterior a la fecha inicial
                if (fechaFinal < fechaInicial) {
                    Swal.fire('Error!', 'La fecha final no puede ser anterior a la fecha inicial.', 'error');
                    return;
                }

                // Si todo es correcto, realizar la petición AJAX
                $.ajax({
                    url: '/mantenimientos',
                    type: 'POST',
                    data: $('#mantenimientoForm').serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#mantenimientoModal').modal('hide');
                            Swal.fire({
                                title: 'Éxito!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = '';
                            if (errors) {
                                errorMessage = 'Se encontraron los siguientes errores:\n';
                                for (let field in errors) {
                                    errorMessage += `${field}: ${errors[field].join(', ')}\n`;
                                }
                            } else if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else {
                                errorMessage = 'Se produjo un error de validación.';
                            }
                            Swal.fire('Error de validación', errorMessage, 'error');
                        } else {
                            Swal.fire('Error!', 'No se pudo agendar el mantenimiento.', 'error');
                        }
                    }
                });
            });

            function findMantenimientoById(id) {
                return mantenimientosData.realizados.find(m => m.id === id) ||
                       mantenimientosData.programados.find(m => m.id === id);
            }

            function showErrorAlert(title, message) {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: message,
                    footer: '<a href>Por favor, contacte al soporte técnico si el problema persiste.</a>'
                });
            }

            function cargarTecnicosAsignados(vehiculoId, tipoComponente) {
                var fechaInicial = $('#fechaInicial').val();
                var fechaFinal = $('#fechaFinal').val();

                $.ajax({
                    url: "{{ route('get.tecnicos.disponibles.asignados') }}",
                    type: 'GET',
                    data: {
                        vehiculo_id: vehiculoId,
                        tipo_componente: tipoComponente,
                        fecha_inicial: fechaInicial,
                        fecha_final: fechaFinal
                    },
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        var select = $('#usuario_id');
                        select.empty();
                        select.append('<option value="">Seleccione un técnico</option>');
                        if (response.tecnicos && response.tecnicos.length > 0) {
                            $.each(response.tecnicos, function(key, value) {
                                select.append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        } else {
                            select.append('<option value="">' + (response.mensaje || 'No hay técnicos disponibles') + '</option>');
                        }

                        if (response.mensaje) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Información',
                                text: response.mensaje
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar técnicos:', xhr);
                        var select = $('#usuario_id');
                        select.empty();
                        select.append('<option value="">Error al cargar técnicos</option>');

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al cargar los técnicos. Por favor, inténtelo de nuevo.'
                        });
                    }
                });
            }

            // Trigger para cargar los técnicos al cambiar el componente
            $('#tipoComponente').change(function() {
                var vehiculoId = $('#vehiculo_id').val();
                var tipoComponente = $(this).val();
                if (vehiculoId && tipoComponente) {
                    cargarTecnicosAsignados(vehiculoId, tipoComponente);
                }
            });

            // Función para limpiar los campos del modal
            function limpiarCamposModal() {
                $('#vehiculo_id').val('');
                $('#vehiculo').val('');
                $('#tipoMantenimiento').val('');
                $('#tipoComponente').val('');
                $('#fechaInicial').val('');
                $('#fechaFinal').val('');
                $('#usuario_id').empty().append('<option value="">Seleccione un técnico</option>');
            }

            // Vincular la función de limpieza al evento de cierre del modal
            $('#mantenimientoModal').on('hidden.bs.modal', function () {
                limpiarCamposModal();
            });

        });
    </script>
@stop
