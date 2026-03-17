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
                <div class="col-md-12 mt-2">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{ request('descripcion') }}" placeholder="Buscar en la descripción...">
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
                <tr>
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

<!-- Modal -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));

    function showDetails(id) {
        document.getElementById('modalBody').innerHTML = '<div class="text-center">Cargando...</div>';
        modal.show();

        fetch(`/sentre/${id}`)
            .then(response => response.json())
            .then(data => {
                let html = `
                    <div class="row">
                        <div class="col-md-6"><p><strong>ID Record:</strong> ${data.record_id}</p></div>
                        <div class="col-md-6"><p><strong>Tipo:</strong> ${data.type}</p></div>
                        <hr>
                        <div class="col-md-12"><p><strong>Expediente:</strong> ${data.expediente || 'N/A'}</p></div>
                        <div class="col-md-12"><p><strong>Descripción:</strong> ${data.descripcion || 'N/A'}</p></div>
                        <hr>
                        <div class="col-md-4"><p><strong>Año Creación:</strong> ${data.anio_creacion || 'N/A'}</p></div>
                        <div class="col-md-4"><p><strong>Inicio:</strong> ${data.fecha_inicio || 'N/A'}</p></div>
                        <div class="col-md-4"><p><strong>Final:</strong> ${data.fecha_final || 'N/A'}</p></div>
                        <hr>
                        <div class="col-md-6"><p><strong>Ubicación Física:</strong> ${data.ubicacion_fisica || 'N/A'}</p></div>
                        <div class="col-md-3"><p><strong>No. Caja:</strong> ${data.no_caja || 'N/A'}</p></div>
                        <div class="col-md-3"><p><strong>T. Conservación:</strong> ${data.tiempo_conservacion || 'N/A'}</p></div>
                        <hr>
                        <div class="col-md-6"><p><strong>Clasificación:</strong> ${data.clasificacion || 'N/A'}</p></div>
                        <div class="col-md-6"><p><strong>Caracter Doc:</strong> ${data.caracter_documental || 'N/A'}</p></div>
                        <hr>
                        <div class="col-md-4"><p><strong>No. Legajos:</strong> ${data.no_legajos || 'N/A'}</p></div>
                        <div class="col-md-4"><p><strong>No. Hojas:</strong> ${data.no_hojas || 'N/A'}</p></div>
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
</script>
</body>
</html>
