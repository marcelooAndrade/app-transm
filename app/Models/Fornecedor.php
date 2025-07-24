<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';

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
        return $this->hasMany(Pedido::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}