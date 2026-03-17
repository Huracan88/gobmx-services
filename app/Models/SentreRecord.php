<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentreRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'sentre_user_id',
        'type',
        'record_id',
        'expediente',
        'descripcion',
        'anio_creacion',
        'ubicacion_fisica',
        'no_caja',
        'fecha_inicio',
        'fecha_final',
        'tiempo_conservacion',
        'fecha_transferencia',
        'clasificacion',
        'caracter_documental',
        'no_legajos',
        'no_hojas',
        'preservacion',
        'observaciones',
    ];

    public function user()
    {
        return $this->belongsTo(SentreUser::class, 'sentre_user_id');
    }
}
