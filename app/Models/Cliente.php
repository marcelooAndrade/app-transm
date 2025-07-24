<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function pedidos()
    {
        return $this->hasMany(PedidoLogistica::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
