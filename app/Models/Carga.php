<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    protected $fillable = ['nome', 'rota_id', 'status'];

    public function pedidos()
    {
        return $this->belongsToMany(PedidoLogistica::class, 'carga_pedido', 'carga_id', 'pedido_id');
    }


    public function rota()
    {
        return $this->belongsTo(Rota::class);
    }
}

