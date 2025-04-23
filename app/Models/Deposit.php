<?php
// app/Models/Deposit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    // Qué campos podemos asignar masivamente
    protected $fillable = [
        'client_phone', 'user_id',
        'brand', 'model', 'serial_number',
        'problem_description', 'more_info',
        'unlock_password', // secuencia de patrón opcional
        'status', 'date_in', 'date_out',
        'last_modification_user_id',  // <— aquí

    ];

    // Relación con Cliente
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_phone', 'phone');
    }

    // Relación con Usuario que creó el depósito
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Usuario que hizo la última modificación (p. ej. entrega)
    public function lastModifier()
    {
        return $this->belongsTo(User::class,'last_modification_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliverer()
    {
        return $this->belongsTo(User::class, 'last_modification_user_id');
    }

}
