<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'tipo',
        'status',
        'ultimo_acesso'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acesso' => 'datetime',
        'tipo' => 'string',
        'status' => 'string'
    ];

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeGerentes($query)
    {
        return $query->where('tipo', 'gerente');
    }

    public function scopeOperacionais($query)
    {
        return $query->where('tipo', 'operacional');
    }

    public function isGerente()
    {
        return $this->tipo === 'gerente';
    }

    public function isOperacional()
    {
        return $this->tipo === 'operacional';
    }

    public function registrarAcesso()
    {
        $this->update(['ultimo_acesso' => now()]);
    }

    public function temPermissao($permissao)
    {
        $permissoes = [
            'gerente' => [
                'dashboard_completo',
                'relatorios_gerenciais',
                'gerenciar_usuarios',
                'exportar_dados',
                'configurar_sistema'
            ],
            'operacional' => [
                'importar_arquivos',
                'atualizar_pedidos',
                'gerar_ordem_servico',
                'consultar_informacoes'
            ]
        ];

        return in_array($permissao, $permissoes[$this->tipo] ?? []);
    }
}