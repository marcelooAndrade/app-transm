<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'tipo',
        'status',
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

    // App\Models\Pessoa.php

    public function setNomeAttribute($value)
    {
        $preposicoes = ['da', 'de', 'do', 'das', 'dos', 'e'];
        $palavras = explode(' ', strtolower($value));

        $nomeFormatado = collect($palavras)->map(function ($palavra, $index) use ($preposicoes) {
            // Sempre coloca a primeira palavra com inicial maiúscula
            if ($index === 0 || !in_array($palavra, $preposicoes)) {
                return ucfirst($palavra);
            }

            return $palavra;
        })->implode(' ');

        $this->attributes['nome'] = $nomeFormatado;
    }
}
