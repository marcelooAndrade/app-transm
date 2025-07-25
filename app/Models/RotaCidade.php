<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RotaCidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'rota_id',
        'cidade',
        'estado',
    ];

    public function rota()
    {
        return $this->belongsTo(Rota::class);
    }


}
