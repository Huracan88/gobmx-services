<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SentreUser;
use App\Models\SentreRecord;

class SentreViewController extends Controller
{
    public function index(Request $request)
    {
        $users = SentreUser::all();
        $selectedUserId = $request->get('user_id');
        $selectedType = $request->get('type');

        $query = SentreRecord::query();

        if ($selectedUserId) {
            $query->where('sentre_user_id', $selectedUserId);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        // Filtros
        if ($request->filled('anio_creacion')) {
            $query->where('anio_creacion', 'like', '%' . $request->get('anio_creacion') . '%');
        }

        if ($request->filled('expediente')) {
            $query->where('expediente', 'like', '%' . $request->get('expediente') . '%');
        }

        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->get('descripcion') . '%');
        }

        if ($request->get('incomplete')) {
            $query->where(function($q) {
                $q->whereNull('no_caja')->orWhere('no_caja', '')
                  ->orWhereNull('fecha_inicio')->orWhere('fecha_inicio', '')
                  ->orWhereNull('fecha_final')->orWhere('fecha_final', '')
                  ->orWhereNull('tiempo_conservacion')->orWhere('tiempo_conservacion', '')
                  ->orWhereNull('no_legajos')->orWhere('no_legajos', '')
                  ->orWhereNull('no_hojas')->orWhere('no_hojas', '');
            });
        }

        $records = $query->paginate(20)->appends($request->all());

        return view('sentre.index', compact('users', 'records', 'selectedUserId', 'selectedType'));
    }

    public function show($id)
    {
        $record = SentreRecord::with('user')->findOrFail($id);
        $record->append('is_incomplete');
        return response()->json($record);
    }

    public function update(Request $request, $id)
    {
        $record = SentreRecord::findOrFail($id);

        $data = $request->validate([
            'fecha_transferencia' => 'nullable|string',
            'expediente' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'anio_creacion' => 'nullable|string',
            'fecha_inicio' => 'nullable|string',
            'fecha_final' => 'nullable|string',
            'tiempo_conservacion' => 'nullable|string',
            'no_legajos' => 'nullable|string',
            'no_hojas' => 'nullable|string',
            'preservacion' => 'nullable|string',
            'ubicacion_fisica' => 'nullable|string',
            'no_caja' => 'nullable|string',
            'clasificacion' => 'nullable|string',
            'caracter_documental' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $record->update($data);

        return response()->json(['code' => '200', 'message' => 'Registro actualizado exitosamente en la base de datos local.']);
    }
}
