<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // Nombre de la tabla
    protected $table = 'clients';

    // Clave primaria personalizada
    protected $primaryKey = 'phone';
    public $incrementing = false;   // phone es string
    protected $keyType = 'string';

    // Campos asignables masivamente
    protected $fillable = [
        'phone',
        'phone_2',          // ← Nuevo campo para teléfono secundario
        'name',
        'surname',
        'additional_info',
    ];

    /**
     * Relación uno a muchos con depósitos.
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'client_phone', 'phone');
    }
}
