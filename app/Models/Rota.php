<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rota extends Model
{
    use HasFactory;

    protected $fillable = [
        'origem',
        'destino',
        'distancia',
        'tempo_medio',
        'valor_base',
        'status',
        'utilizacoes'
    ];

    protected $casts = [
        'status' => 'string',
        'valor_base' => 'decimal:2',
        'distancia' => 'integer',
        'utilizacoes' => 'integer'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('status', 'ativa');
    }

    public function incrementarUtilizacao()
    {
        $this->increment('utilizacoes');
    }

    public function getRotaCompleta()
    {
        return $this->origem . ' → ' . $this->destino;
    }
}