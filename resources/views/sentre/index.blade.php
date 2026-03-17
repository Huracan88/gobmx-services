<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentre Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .card { margin-bottom: 20px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .table-responsive { background: white; border-radius: 8px; }
        .row-incomplete { border-left: 5px solid #dc3545; background-color: #fff8f8; }
        .text-missing { color: #dc3545; font-weight: bold; font-size: 0.85em; }
        .pagination svg { width: 1.25rem; height: 1.25rem; }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <h2 class="mb-4">Visualización de Información Sentre</h2>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('sentre.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="user_id" class="form-label">Usuario</label>
                    <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                {{ $user->username }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Tipo</label>
                    <select name="type" id="type" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="tramite" {{ $selectedType == 'tramite' ? 'selected' : '' }}>Trámite</option>
                        <option value="concentracion" {{ $selectedType == 'concentracion' ? 'selected' : '' }}>Concentración</option>
                        <option value="baja" {{ $selectedType == 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="historico" {{ $selectedType == 'historico' ? 'selected' : '' }}>Histórico</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="anio_creacion" class="form-label">Año Creación</label>
                    <input type="text" name="anio_creacion" id="anio_creacion" class="form-control" value="{{ request('anio_creacion') }}" placeholder="Ej. 2024">
                </div>
                <div class="col-md-2">
                    <label for="expediente" class="form-label">Expediente</label>
                    <input type="text" name="expediente" id="expediente" class="form-control" value="{{ request('expediente') }}" placeholder="Buscar...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-9 mt-2">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{ request('descripcion') }}" placeholder="Buscar en la descripción...">
                </div>
                <div class="col-md-3 mt-2 d-flex align-items-end pb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="incomplete" id="incomplete" value="1" {{ request('incomplete') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="incomplete">Expediente incompleto</label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Expediente</th>
                <th>Descripción</th>
                <th>Año</th>
                <th>Ubicación</th>
                <th>Caja</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($records as $record)
                <tr class="{{ $record->is_incomplete ? 'row-incomplete' : '' }}">
                    <td>{{ $record->record_id }}</td>
                    <td>{{ $record->expediente }}</td>
                    <td>{{ Str::limit($record->descripcion, 50) }}</td>
                    <td>{{ $record->anio_creacion }}</td>
                    <td>{{ $record->ubicacion_fisica }}</td>
                    <td>{{ $record->no_caja }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($record->type) }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info text-white" onclick="showDetails({{ $record->id }})">
                            Detalles
                        </button>
                        <button type="button" class="btn btn-sm btn-warning text-white" onclick="editRecord({{ $record->id }})">
                            Editar
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="sync-btn-{{ $record->record_id }}" onclick="syncRemote('{{ $record->record_id }}')">
                            Sincronizar
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No se encontraron registros.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $records->links() }}
        </div>
    </div>
</div>

<!-- Modal Detalles -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Detalles del Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                @csrf
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_expediente" class="form-label">Expediente</label>
                            <input type="text" class="form-control" id="edit_expediente" name="expediente">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_fecha_transferencia" class="form-label">Fecha Transferencia</label>
                            <input type="text" class="form-control" id="edit_fecha_transferencia" name="fecha_transferencia">
                        </div>
                        <div class="col-md-12">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_anio_creacion" class="form-label">Año Creación</label>
                            <input type="text" class="form-control" id="edit_anio_creacion" name="anio_creacion">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="text" class="form-control" id="edit_fecha_inicio" name="fecha_inicio">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_fecha_final" class="form-label">Fecha Final</label>
                            <input type="text" class="form-control" id="edit_fecha_final" name="fecha_final">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_ubicacion_fisica" class="form-label">Ubicación Física</label>
                            <input type="text" class="form-control" id="edit_ubicacion_fisica" name="ubicacion_fisica">
                        </div>
                        <div class="col-md-3">
                            <label for="edit_no_caja" class="form-label">No. Caja</label>
                            <input type="text" class="form-control" id="edit_no_caja" name="no_caja">
                        </div>
                        <div class="col-md-3">
                            <label for="edit_tiempo_conservacion" class="form-label">T. Conservación</label>
                            <input type="text" class="form-control" id="edit_tiempo_conservacion" name="tiempo_conservacion">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_clasificacion" class="form-label">Clasificación</label>
                            <select class="form-select" id="edit_clasificacion" name="clasificacion">
                                <option value="PÚBLICO">PÚBLICO</option>
                                <option value="RESERVADO">RESERVADO</option>
                                <option value="CONFIDENCIAL">CONFIDENCIAL</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_caracter_documental" class="form-label">Caracter Doc</label>
                            <select class="form-select" id="edit_caracter_documental" name="caracter_documental">
                                <option value="LEGAL">LEGAL</option>
                                <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                <option value="FISCAL">FISCAL</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_no_legajos" class="form-label">No. Legajos</label>
                            <input type="text" class="form-control" id="edit_no_legajos" name="no_legajos">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_no_hojas" class="form-label">No. Hojas</label>
                            <input type="text" class="form-control" id="edit_no_hojas" name="no_hojas">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Preservación</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="preservacion" id="edit_preservacion_s" value="S">
                                <label class="form-check-label" for="edit_preservacion_s">S (Si)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="preservacion" id="edit_preservacion_n" value="N">
                                <label class="form-check-label" for="edit_preservacion_n">N (No)</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));

    function showDetails(id) {
        document.getElementById('modalBody').innerHTML = '<div class="text-center">Cargando...</div>';
        modal.show();

        const missing = '<span class="text-missing">Faltante</span>';

        fetch(`/sentre/${id}`)
            .then(response => response.json())
            .then(data => {
                const checkMissing = (val) => val ? val : missing;

                let html = `
                    <div class="row">
                        <div class="col-md-6"><p><strong>ID Record:</strong> ${data.record_id}</p></div>
                        <div class="col-md-6"><p><strong>Tipo:</strong> ${data.type}</p></div>
                        <hr>
                        <div class="col-md-12"><p><strong>Expediente:</strong> ${data.expediente || 'N/A'}</p></div>
                        <div class="col-md-12"><p><strong>Descripción:</strong> ${data.descripcion || 'N/A'}</p></div>
                        <hr>
                        <div class="col-md-4"><p><strong>Año Creación:</strong> ${data.anio_creacion || 'N/A'}</p></div>
                        <div class="col-md-4"><p><strong>Inicio:</strong> ${checkMissing(data.fecha_inicio)}</p></div>
                        <div class="col-md-4"><p><strong>Final:</strong> ${checkMissing(data.fecha_final)}</p></div>
                        <hr>
                        <div class="col-md-6"><p><strong>Ubicación Física:</strong> ${data.ubicacion_fisica || 'N/A'}</p></div>
                        <div class="col-md-3"><p><strong>No. Caja:</strong> ${checkMissing(data.no_caja)}</p></div>
                        <div class="col-md-3"><p><strong>T. Conservación:</strong> ${checkMissing(data.tiempo_conservacion)}</p></div>
                        <hr>
                        <div class="col-md-6"><p><strong>Clasificación:</strong> ${data.clasificacion || 'N/A'}</p></div>
                        <div class="col-md-6"><p><strong>Caracter Doc:</strong> ${data.caracter_documental || 'N/A'}</p></div>
                        <hr>
                        <div class="col-md-4"><p><strong>No. Legajos:</strong> ${checkMissing(data.no_legajos)}</p></div>
                        <div class="col-md-4"><p><strong>No. Hojas:</strong> ${checkMissing(data.no_hojas)}</p></div>
                        <div class="col-md-4"><p><strong>Preservación:</strong> ${data.preservacion || 'N/A'}</p></div>
                        <div class="col-md-12 mt-2">
                            <p><strong>Observaciones:</strong></p>
                            <p class="p-2 border rounded bg-light">${data.observaciones || 'Sin observaciones'}</p>
                        </div>
                    </div>
                `;
                document.getElementById('modalBody').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('modalBody').innerHTML = '<div class="alert alert-danger">Error al cargar los detalles.</div>';
            });
    }

    function editRecord(id) {
        fetch(`/sentre/${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_expediente').value = data.expediente || '';
                document.getElementById('edit_fecha_transferencia').value = data.fecha_transferencia || '';
                document.getElementById('edit_descripcion').value = data.descripcion || '';
                document.getElementById('edit_anio_creacion').value = data.anio_creacion || '';
                document.getElementById('edit_fecha_inicio').value = data.fecha_inicio || '';
                document.getElementById('edit_fecha_final').value = data.fecha_final || '';
                document.getElementById('edit_ubicacion_fisica').value = data.ubicacion_fisica || '';
                document.getElementById('edit_no_caja').value = data.no_caja || '';
                document.getElementById('edit_tiempo_conservacion').value = data.tiempo_conservacion || '';
                document.getElementById('edit_clasificacion').value = data.clasificacion || '';
                document.getElementById('edit_caracter_documental').value = data.caracter_documental || '';
                document.getElementById('edit_no_legajos').value = data.no_legajos || '';
                document.getElementById('edit_no_hojas').value = data.no_hojas || '';

                if (data.preservacion === 'S') {
                    document.getElementById('edit_preservacion_s').checked = true;
                } else if (data.preservacion === 'N') {
                    document.getElementById('edit_preservacion_n').checked = true;
                } else {
                    document.getElementById('edit_preservacion_s').checked = false;
                    document.getElementById('edit_preservacion_n').checked = false;
                }

                document.getElementById('edit_observaciones').value = data.observaciones || '';
                editModal.show();
            });
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit_id').value;
        const btn = document.getElementById('saveBtn');
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        btn.disabled = true;
        btn.innerText = 'Guardando...';

        fetch(`/sentre/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.code === '200') {
                alert(result.message);
                location.reload();
            } else {
                alert('Error al guardar: ' + result.message);
            }
        })
        .catch(error => {
            alert('Error de comunicación.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Guardar Cambios';
        });
    });

    function syncRemote(id) {
        const btn = document.getElementById(`sync-btn-${id}`);
        const originalText = btn.innerText;

        const user = prompt("Ingresa el usuario de Sentre:");
        if (!user) return;
        const password = prompt("Ingresa la contraseña de Sentre:");
        if (!password) return;

        btn.disabled = true;
        btn.innerText = 'Sincronizando...';

        fetch('/api/sentre-sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                sentre_user: user,
                sentre_password: password,
                record_id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.code === '200') {
                alert('Sincronización exitosa: ' + data.message);
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            } else {
                alert('Error al sincronizar: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error en la comunicación con el servidor.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }
</script>
</body>
</html>
