<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCaminhao extends Model
{
    use HasFactory;

    protected $table = 'tipos_caminhao';

    protected $fillable = [
        'nome',
        'descricao',
        'capacidade_toneladas',
        'capacidade_paletes',
        'valor_km',
        'status',
        'utilizacoes'
    ];

    protected $casts = [
        'status' => 'string',
        'capacidade_toneladas' => 'decimal:2',
        'capacidade_paletes' => 'integer',
        'valor_km' => 'decimal:2',
        'utilizacoes' => 'integer'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function incrementarUtilizacao()
    {
        $this->increment('utilizacoes');
    }

    public function calcularValorRota($distancia)
    {
        return $this->valor_km * $distancia;
    }
}