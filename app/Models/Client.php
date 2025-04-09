<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'phone';
    public $incrementing = false; // phone es string
    protected $keyType = 'string';

    protected $fillable = [
        'phone', 'name', 'surname', 'additional_info'
    ];

    // Relación con Deposits
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'client_phone', 'phone');
    }
}
