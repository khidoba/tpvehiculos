@extends('adminlte::page')

@section('title', 'Calendario de Mantenimientos')

@section('content_header')
    <h1>Calendario de Mantenimientos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(auth()->user()->hasRole('Administrador de Sistema'))
                <h3>Calendario de mantenimientos - Todas las agencias</h3>
            @else
                <h3>Calendario de mantenimientos - {{ auth()->user()->agencia }}</h3>
            @endif
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Modal para registrar mantenimiento -->
    <div class="modal fade" id="mantenimientoModal" tabindex="-1" role="dialog" aria-labelledby="mantenimientoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mantenimientoModalLabel">Registro de Mantenimiento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="mantenimientoForm">
                        @csrf
                        <input type="hidden" id="eventoId" name="eventoId">
                        <input type="hidden" id="fecha_hora" name="fecha_hora">

                        <div class="form-group">
                            <label for="fase">Fase del mantenimiento:</label>
                            <select class="form-control" id="fase" name="fase" required>
                                <option value="diagnostico">Diagnóstico inicial</option>
                                <option value="ejecucion">Ejecución</option>
                                <option value="entrega">Entrega y pruebas</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="acciones">Acciones realizadas:</label>
                            <textarea class="form-control" id="acciones" name="acciones" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="materiales">Materiales utilizados:</label>
                            <select class="form-control select2" id="materiales" name="materiales[]" multiple>
                                <!-- Las opciones se cargarán dinámicamente -->
                            </select>
                        </div>

                        <div id="cantidadesContainer">
                            <!-- Los campos de cantidad se generarán dinámicamente -->
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones:</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Foto de evidencia:</label>
                            <div class="mt-2">
                                <input type="file" id="fotoInput" name="foto" accept="image/*" capture="environment" style="display: none;">
                                <video id="cameraPreview" style="display: none; width: 100%; max-width: 500px;" autoplay></video>
                                <canvas id="photoCanvas" style="display: none;"></canvas>
                                <img id="photoPreview" style="display: none; max-width: 100%;" alt="Vista previa de la foto">
                                <button type="button" class="btn btn-primary" id="capturePhotoBtn">Capturar Foto</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarMantenimiento">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para rechazar mantenimiento -->
    <div class="modal fade" id="rechazoModal" tabindex="-1" role="dialog" aria-labelledby="rechazoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rechazoModalLabel">Rechazar Mantenimiento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="rechazoForm">
                        <input type="hidden" id="rechazoEventoId" name="eventoId">
                        <div class="form-group">
                            <label for="rechazoRazon">Razón del rechazo:</label>
                            <textarea class="form-control" id="rechazoRazon" name="rechazo_razon" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-danger" id="confirmarRechazo">Rechazar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.1/tippy.css" />
    <style>
        .fc-sun { background-color: #f8d7da; }
        .fc-holiday { background-color: #ffeeba; }
        .holiday-name { font-size: 0.8em; color: #856404; }
        .correctivo-event { background-color: #dc3545 !important; border-color: #dc3545 !important; }
        .fc-event-icon { margin-right: 5px; }
        .fc-event.rechazado {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        #cantidadesContainer .form-group {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        #cantidadesContainer label {
            font-weight: bold;
        }
        .select2-container--default .select2-selection--multiple {
            border-color: #ced4da;
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src='https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js'></script>
    <script src='https://unpkg.com/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js'></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            var panameHolidays = [
                {date: '2024-01-01', name: 'Año Nuevo'},
                {date: '2024-01-09', name: 'Día de los Mártires'},
                // ... más días festivos
            ];

            // Establecer la fecha mínima para horaInicio y horaFin
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = today.getFullYear();

            today = yyyy + '-' + mm + '-' + dd + 'T00:00';
            $('#horaInicio, #horaFin').attr('min', today);

            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultView: 'month',
                navLinks: true,
                editable: false,
                eventLimit: true,

                events: {
                    url: '/calendario/eventos',
                    type: 'GET',
                    data: function() {
                        return {
                            user_id: '{{ auth()->id() }}',
                            role: '{{ auth()->user()->roles->first()->name }}'
                        };
                    },
                    error: function() {
                        alert('Hubo un error al cargar los eventos!');
                    }
                },

                eventRender: function(event, element) {
                    // Obtener solo la parte de la fecha en formato 'YYYY-MM-DD'
                    var currentDate = moment().format('YYYY-MM-DD');
                    var eventDate = moment(event.start).format('YYYY-MM-DD');
                    var registerLink = '';

                    // Verificar si se puede registrar material
                    if (event.canRegisterMaterial) {
                        // Comparar solo las fechas sin horas
                        if (eventDate >= currentDate) {
                            var linkText = (eventDate === currentDate) ? "Registrar mantenimiento (Hoy)" : "Registrar mantenimiento";
                            registerLink = `<a href="#" class="register-material" data-event-id="${event.id}">${linkText}</a>`;
                        } else {
                            // Si el evento es en el pasado
                            registerLink = `<span class="text-muted">Evento pasado</span>`;
                        }
                    } else {
                        registerLink = '<span class="text-muted">No se puede registrar más información</span>';
                    }

                    var color = getColorByEstado(event.estado);

                    // Aplicar el color al elemento del evento
                    element.css('background-color', color);
                    element.css('border-color', color);

                    if (event.estado === 'rechazado') {
                        element.addClass('rechazado');
                    }

                    // Icono del evento según la categoría
                    var iconClass = event.categoria === 'vehiculo' ? 'fas fa-car' :
                                    (event.categoria === 'refrigeracion' ? 'fas fa-snowflake' : '');
                    if (iconClass) {
                        element.find('.fc-title').prepend($('<i>').addClass(iconClass + ' fc-event-icon'));
                    }

                    if (event.overlapping) {
                        element.css('opacity', '0.7');
                        element.find('.fc-title').prepend('<i class="fas fa-exclamation-triangle"></i> ');
                    }

                    element.attr('title', '');

                    // Tooltip con la información del evento

                    tippy(element[0], {
                        content: function() {
                            // Obtener solo la parte de la fecha en formato 'YYYY-MM-DD'
                            var currentDate = moment().format('YYYY-MM-DD');
                            var eventDate = moment(event.start).format('YYYY-MM-DD');
                            var registerLink = '';

                            // Comparar solo las fechas sin horas
                            if (event.canRegisterMaterial) {
                                if (eventDate >= currentDate) {
                                    var linkText = (eventDate === currentDate) ? "Registrar mantenimiento (Hoy)" : "Registrar mantenimiento";
                                    registerLink = `<a href="#" class="register-material" data-event-id="${event.id}">${linkText}</a>`;
                                } else {
                                    registerLink = `<span class="text-muted">Evento pasado</span>`;
                                }
                            } else {
                                registerLink = '<span class="text-muted">No se puede registrar más información</span>';
                            }

                            var stateInfo = '';
                            if (event.estado === 'rechazado') {
                                stateInfo = `<p><strong>Estado:</strong> Rechazado</p>
                                             <p><strong>Razón del rechazo:</strong> ${event.rechazo_razon || 'No especificada'}</p>`;
                            } else {
                                stateInfo = `<p><strong>Estado:</strong> ${event.estado}</p>`;
                            }

                            var approveRejectButtons = '';
                            if (event.estado === 'realizado' && event.canApproveOrReject) {
                                approveRejectButtons = `
                                    <button class="btn btn-sm btn-primary aprobar-btn" data-event-id="${event.id}">Aprobar</button>
                                    <button class="btn btn-sm btn-danger rechazar-btn" data-event-id="${event.id}">Rechazar</button>
                                `;
                            }

                            return `
                                <div class="event-tooltip">
                                    <h4>${event.title}</h4>
                                    <p><strong>Vehículo:</strong> ${event.vehiculo || 'No especificado'}</p>
                                    <p><strong>Placa:</strong> ${event.placa || 'No especificada'}</p>
                                    <p><strong>Inicio:</strong> ${moment(event.start).format('DD/MM/YYYY')}</p>
                                    ${event.end ? `<p><strong>Fin:</strong> ${moment(event.end).format('DD/MM/YYYY')}</p>` : ''}
                                    <p><strong>Actividad:</strong> ${event.actividad || 'No especificada'}</p>
                                    ${stateInfo}
                                    <p>${event.description || 'Sin descripción'}</p>
                                    <p>${registerLink}</p>
                                    ${approveRejectButtons}
                                </div>
                            `;
                        },
                        allowHTML: true,
                        interactive: true,
                        placement: 'top',
                        arrow: true,
                        theme: 'light-border',
                        maxWidth: 300,
                        appendTo: document.body,
                        onShown(instance) {
                            // Registrar mantenimiento
                            $('.register-material', instance.popper).on('click', function(e) {
                                e.preventDefault();
                                var eventId = $(this).data('event-id');
                                $('#eventoId').val(eventId);
                                $('#mantenimientoModal').modal('show');
                            });

                            // Aprobar mantenimiento
                            $('.aprobar-btn', instance.popper).on('click', function(e) {
                                e.preventDefault();
                                aprobarMantenimiento($(this).data('event-id'));
                            });

                            // Rechazar mantenimiento
                            $('.rechazar-btn', instance.popper).on('click', function(e) {
                                e.preventDefault();
                                $('#rechazoEventoId').val($(this).data('event-id'));
                                $('#rechazoModal').modal('show');
                            });
                        }
                    });

                },

                dayRender: function(date, cell) {
                    if (date.day() === 0) cell.addClass('fc-sun');
                    var formattedDate = date.format('YYYY-MM-DD');
                    var holiday = panameHolidays.find(h => h.date === formattedDate);
                    if (holiday) {
                        cell.addClass('fc-holiday');
                        cell.append('<div class="holiday-name">' + holiday.name + '</div>');
                    }
                }
            });

            $('#materiales').select2({
                placeholder: 'Seleccione los materiales utilizados',
                allowClear: true,
                data: [], // Inicialmente vacío, lo llenaremos con AJAX
                minimumInputLength: 0,
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todos los resultados
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Buscar en el texto del material
                    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }

                    // Si no hay coincidencia, devolver null
                    return null;
                }
            }).on('change', function() {
                var selectedMaterials = $(this).val();
                var container = $('#cantidadesContainer');

                // Iterar sobre todos los materiales seleccionados
                $.each(selectedMaterials, function(index, materialId) {
                    var materialName = $('#materiales option[value="' + materialId + '"]').text();
                    var existingField = container.find('#cantidad_' + materialId);

                    // Si el campo no existe, créalo
                    if (existingField.length === 0) {
                        container.append(`
                            <div class="form-group" id="material_group_${materialId}">
                                <label for="cantidad_${materialId}">Cantidad de ${materialName}:</label>
                                <input type="number" class="form-control cantidad-material" id="cantidad_${materialId}" name="cantidades[]" required min="1">
                            </div>
                        `);
                    }
                });

                // Eliminar campos de materiales que ya no están seleccionados
                container.find('.form-group').each(function() {
                    var fieldId = $(this).attr('id').split('_')[2];
                    if (selectedMaterials.indexOf(fieldId) === -1) {
                        $(this).remove();
                    }
                });
            });

            // Cargar todos los materiales al inicio
            $.ajax({
                url: '/calendario/materiales',
                dataType: 'json',
                success: function(data) {
                    // Formatear los datos para Select2
                    var formattedData = data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    });

                    // Establecer los datos en Select2
                    $('#materiales').select2('destroy').empty().select2({
                        data: formattedData,
                        placeholder: 'Seleccione los materiales utilizados',
                        allowClear: true,
                        minimumInputLength: 0,
                        matcher: function(params, data) {
                            // Si no hay término de búsqueda, mostrar todos los resultados
                            if ($.trim(params.term) === '') {
                                return data;
                            }

                            // Buscar en el texto del material
                            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                                return data;
                            }

                            // Si no hay coincidencia, devolver null
                            return null;
                        }
                    });
                }
            });

            // Validación del formulario
            $('#mantenimientoForm').validate({
                rules: {
                    fase: "required",
                    diagnostico: "required",
                    acciones: "required",
                    "cantidades[]": {
                        required: true,
                        min: 1
                    }
                },
                messages: {
                    fase: "Por favor, seleccione la fase del mantenimiento",
                    diagnostico: "Por favor, ingrese el diagnóstico inicial",
                    acciones: "Por favor, ingrese las acciones realizadas",
                    "cantidades[]": {
                        min: "La cantidad debe ser al menos 1"
                    }
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });

            $('#guardarMantenimiento').click(function() {
                if ($('#mantenimientoForm').valid()) {
                    var formData = new FormData($('#mantenimientoForm')[0]);

                    // Establecer la fecha y hora actual
                    var fechaHora = moment().format('YYYY-MM-DD HH:mm:ss');
                    $('#fecha_hora').val(fechaHora);
                    formData.set('fecha_hora', fechaHora);

                    console.log('Datos que se enviarán al servidor:');
                    for (var pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }

                    // Agregar la foto al formData
                    if (photoPreview.src) {
                        fetch(photoPreview.src)
                            .then(res => res.blob())
                            .then(blob => {
                                formData.append('foto', blob, 'foto.jpg');
                                sendMantenimientoData(formData);
                            });
                    } else if (fotoInput.files.length > 0) {
                        formData.append('foto', fotoInput.files[0]);
                        sendMantenimientoData(formData);
                    } else {
                        sendMantenimientoData(formData);
                    }
                }
            });

            function sendMantenimientoData(formData) {
                $.ajax({
                    url: '/calendario/registrar-mantenimiento',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        if (response.success) {
                            $('#mantenimientoModal').modal('hide');
                            Swal.fire({
                                title: 'Éxito!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Actualizar el evento en el calendario
                                    var event = $('#calendar').fullCalendar('clientEvents', formData.get('eventoId'))[0];
                                    if (event) {
                                        event.estado = response.estado;
                                        $('#calendar').fullCalendar('updateEvent', event);
                                    } else {
                                        $('#calendar').fullCalendar('refetchEvents');
                                    }
                                }
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al registrar mantenimiento:', xhr.responseText);
                        console.log('Estado de la respuesta:', xhr.status);
                        console.log('Texto de la respuesta:', xhr.responseText);
                        var errorMessage = 'Hubo un problema al registrar el mantenimiento';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 422) {
                            errorMessage = 'Error de validación. Por favor, revise los datos ingresados.';
                        }
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }

            function getColorByEstado(estado) {
                switch (estado) {
                    case 'programado':
                        return '#dc3545'; // Rojo
                    case 'en_progreso':
                        return '#ffc107'; // Amarillo
                    case 'realizado':
                        return '#28a745'; // Verde
                    case 'aprobado':
                        return '#007bff'; // Azul
                    case 'rechazado':
                        return '#6c757d'; // Gris
                    default:
                        return '#6c757d'; // Gris por defecto
                }
            }

            function enviarDatosAlServidor(formData) {
                $.ajax({
                    url: '/calendario/registrar-mantenimiento',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        if (response.success) {
                            $('#mantenimientoModal').modal('hide');
                            Swal.fire({
                                title: 'Éxito!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#calendar').fullCalendar('refetchEvents');
                                }
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al registrar mantenimiento:', xhr.responseText);
                        var errorMessage = 'Hubo un problema al registrar el mantenimiento';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 422) {
                            errorMessage = 'Error de validación. Por favor, revise los datos ingresados.';
                        }
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }

            // Aprobar mantenimiento
            function aprobarMantenimiento(eventId) {
                $.ajax({
                    url: '/calendario/aprobar-mantenimiento/' + eventId,
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Éxito', 'Mantenimiento aprobado con éxito y vehículo activado', 'success');
                            $('#calendar').fullCalendar('refetchEvents');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al aprobar mantenimiento:', error);
                        Swal.fire('Error', 'Hubo un problema al aprobar el mantenimiento', 'error');
                    }
                });
            }

            // Rechazar mantenimiento
            $('#confirmarRechazo').on('click', function() {
                var eventoId = $('#rechazoEventoId').val();
                var rechazoRazon = $('#rechazoRazon').val();

                if (!rechazoRazon) {
                    Swal.fire('Error', 'Por favor, proporcione una razón para el rechazo.', 'error');
                    return;
                }

                $.ajax({
                    url: `/calendario/rechazar-mantenimiento/${eventoId}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        rechazo_razon: rechazoRazon
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#rechazoModal').modal('hide');
                            $('#calendar').fullCalendar('refetchEvents');
                            Swal.fire('Éxito', 'Mantenimiento rechazado con éxito', 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al rechazar mantenimiento:', xhr);
                        Swal.fire('Error', 'Hubo un problema al rechazar el mantenimiento', 'error');
                    }
                });
            });

            // Configuración de la cámara y captura de fotos
            const cameraPreview = document.getElementById('cameraPreview');
            const photoCanvas = document.getElementById('photoCanvas');
            const photoPreview = document.getElementById('photoPreview');
            const capturePhotoBtn = document.getElementById('capturePhotoBtn');
            const fotoInput = document.getElementById('fotoInput');

            let stream;

            function hasGetUserMedia() {
                return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
            }

            function startCamera() {
                if (hasGetUserMedia()) {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                        .then(function(videoStream) {
                            stream = videoStream;
                            cameraPreview.srcObject = stream;
                            cameraPreview.style.display = 'block';
                            photoPreview.style.display = 'none';
                            capturePhotoBtn.textContent = 'Capturar Foto';
                        })
                        .catch(function(error) {
                            console.error("No se pudo acceder a la cámara:", error);
                            Swal.fire('Error', 'No se pudo acceder a la cámara. Por favor, seleccione una imagen manualmente.', 'error');
                            fotoInput.click();
                        });
                } else {
                    fotoInput.click();
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                    cameraPreview.style.display = 'none';
                }
            }

            function capturePhoto() {
                if (stream) {
                    photoCanvas.width = cameraPreview.videoWidth;
                    photoCanvas.height = cameraPreview.videoHeight;
                    photoCanvas.getContext('2d').drawImage(cameraPreview, 0, 0);
                    photoPreview.src = photoCanvas.toDataURL('image/jpeg');
                    photoPreview.style.display = 'block';
                    cameraPreview.style.display = 'none';
                    stopCamera();
                    capturePhotoBtn.textContent = 'Tomar otra foto';
                }
            }

            capturePhotoBtn.addEventListener('click', function() {
                if (stream) {
                    capturePhoto();
                } else {
                    startCamera();
                }
            });

            fotoInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreview.src = e.target.result;
                        photoPreview.style.display = 'block';
                        cameraPreview.style.display = 'none';
                        capturePhotoBtn.textContent = 'Tomar otra foto';
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            $('#mantenimientoModal').on('hidden.bs.modal', function () {
                stopCamera();
                photoPreview.style.display = 'none';
                cameraPreview.style.display = 'none';
                photoPreview.src = '';
                fotoInput.value = '';
                capturePhotoBtn.textContent = 'Capturar Foto';
            });

            // Cuando el modal de mantenimiento se abre, cargar los técnicos disponibles para el vehículo
            $('#mantenimientoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Botón que disparó el modal
                var vehiculoId = button.data('vehiculo-id'); // Obtener ID del vehículo

                // Limpiar la lista de técnicos
                $('#usuario_id').empty().append('<option value="">Seleccione un técnico</option>');

                // Hacer la petición AJAX para obtener los técnicos disponibles
                $.ajax({
                    url: '/get-tecnicos-disponibles',
                    type: 'GET',
                    data: {
                        vehiculo_id: vehiculoId,
                        fecha_inicial: $('#fechaInicial').val(),
                        fecha_final: $('#fechaFinal').val()
                    },
                    success: function(response) {
                        // Llenar el select con los técnicos disponibles
                        $.each(response.tecnicos, function(key, tecnico) {
                            $('#usuario_id').append(new Option(tecnico.name, tecnico.id));
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los técnicos disponibles.', 'error');
                    }
                });
            });

            // Función para enviar datos del formulario incluyendo la foto
            function sendFormData(formData) {
                $.ajax({
                    url: '/calendario/registrar-mantenimiento',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#mantenimientoModal').modal('hide');
                        $('#calendar').fullCalendar('refetchEvents');
                        Swal.fire('Éxito', 'Mantenimiento registrado con éxito', 'success');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al guardar:', xhr.responseJSON);
                        if (xhr.status === 422) {
                            let errorMessage = 'Se encontraron los siguientes errores:\n';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += `${key}: ${value}\n`;
                            });
                            Swal.fire('Error de validación', errorMessage, 'error');
                        } else {
                            Swal.fire('Error', xhr.responseJSON.message || 'Hubo un problema al registrar el mantenimiento', 'error');
                        }
                    }
                });
            }

            // Configurar CSRF token para todas las solicitudes AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Función para manejar el guardado de mantenimiento incluyendo la foto
            $('#guardarMantenimiento').click(function() {
                if ($('#mantenimientoForm').valid()) {
                    var formData = new FormData($('#mantenimientoForm')[0]);

                    // Agregar la foto al formData
                    if (photoPreview.src) {
                        fetch(photoPreview.src)
                            .then(res => res.blob())
                            .then(blob => {
                                formData.append('foto', blob, 'foto.jpg');
                                sendFormData(formData);
                            });
                    } else if (fotoInput.files.length > 0) {
                        formData.append('foto', fotoInput.files[0]);
                        sendFormData(formData);
                    } else {
                        Swal.fire('Error', 'Por favor, capture o seleccione una foto antes de guardar.', 'error');
                    }
                }
            });
        });
    </script>
@stop
