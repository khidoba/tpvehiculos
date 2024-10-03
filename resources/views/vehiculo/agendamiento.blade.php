<!-- Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="maintenanceModalLabel">Agendar Mantenimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="maintenanceForm">
          @csrf
          <input type="hidden" id="vehicleId" name="vehicle_id">
          <div class="mb-3">
            <label for="vehicleSelect" class="form-label">Vehículo</label>
            <select class="form-select" id="vehicleSelect" name="vehicle_id" required>
              <option value="">Seleccionar vehículo</option>
              @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}">{{ $vehicle->placa }} - {{ $vehicle->modelo }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="technicianSelect" class="form-label">Técnico</label>
            <select class="form-select" id="technicianSelect" name="technician_id" required>
              <option value="">Seleccionar técnico</option>
              @foreach($technicians as $technician)
                <option value="{{ $technician->id }}">{{ $technician->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="maintenanceDate" class="form-label">Fecha de Mantenimiento</label>
            <input type="date" class="form-control" id="maintenanceDate" name="maintenance_date" required>
          </div>
          <div class="mb-3">
            <label for="maintenanceType" class="form-label">Tipo de Mantenimiento</label>
            <select class="form-select" id="maintenanceType" name="maintenance_type" required>
              <option value="">Seleccionar tipo</option>
              <option value="preventivo">Preventivo</option>
              <option value="correctivo">Correctivo</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="scheduleMaintenanceBtn">Agendar</button>
      </div>
    </div>
  </div>
</div>
