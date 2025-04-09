<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';

    protected $fillable = [
        'client_phone',
        'user_id',
        'brand',
        'model',
        'serial_number',
        'problem_description',
        'status',
        'date_in',
        'date_out'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_phone', 'phone');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
