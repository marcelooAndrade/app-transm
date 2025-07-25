<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoLogistica extends Model
{
    use HasFactory;

    protected $table = 'pedido_logisticas';

    protected $fillable = [
        'representante',
        'data_pedido',
        'cliente',
        'numero_pedido',
        'codigo_produto',
        'descricao_produto',
        'industria',
        'qtd_pallets',
        'tipo_produto',
        'total_m2',
        'peso_total',
        'cidade',
        'estado',
        'status'
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'total_m2' => 'decimal:2',
        'peso_total' => 'decimal:2',
        'qtd_pallets' => 'integer',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function rota()
    {
        return $this->belongsTo(Rota::class);
    }

    public function cargas()
    {
        return $this->belongsToMany(Carga::class, 'carga_pedido', 'pedido_id', 'carga_id');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEmTransito($query)
    {
        return $query->where('status', 'em_transito');
    }

    public function scopeEntregues($query)
    {
        return $query->where('status', 'entregue');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function marcarComoEntregue()
    {
        $this->update([
            'status' => 'entregue',
            'data_entrega_real' => now()->toDateString()
        ]);
    }

    public function calcularMargemLucro()
    {
        return $this->valor_frete - $this->valor_frete_motorista;
    }
}
