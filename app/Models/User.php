<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable; // opcional, si quieres notificaciones

    protected $table = 'users'; 
    // Por convención, Laravel usaría 'users', así que esto puede omitirse si quieres

    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    // Si quieres que Eloquent hashee la contraseña automáticamente al asignarla:
    // public function setPasswordAttribute($value)
    // {
    //     $this->attributes['password'] = bcrypt($value);
    // }
}
