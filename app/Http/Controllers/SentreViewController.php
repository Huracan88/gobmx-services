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

        $records = $query->paginate(20)->appends($request->all());

        return view('sentre.index', compact('users', 'records', 'selectedUserId', 'selectedType'));
    }

    public function show($id)
    {
        $record = SentreRecord::with('user')->findOrFail($id);
        return response()->json($record);
    }
}
