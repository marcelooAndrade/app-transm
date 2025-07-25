<?php

namespace App\Models;

use App\Models\RotaCidade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rota extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function cidades()
    {
        return $this->hasMany(RotaCidade::class);
    }

}
