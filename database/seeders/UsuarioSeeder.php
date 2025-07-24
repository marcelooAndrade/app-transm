<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@transm.com.br'],
            [
                'name' => 'Administrador',
                'email' => 'admin@transm.com.br',
                'password' => Hash::make('transm@2025'),
                'cargo' => 'Administrador Geral',
                'tipo' => 'gerente',
                'status' => 'ativo',
                'ultimo_acesso' => now(),
            ]
        );

        // Colaborador
        User::updateOrCreate(
            ['email' => 'colaborador@transm.com.br'],
            [
                'name' => 'Colaborador',
                'email' => 'colaborador@transm.com.br',
                'password' => Hash::make('transm@2025'),
                'cargo' => 'Logística',
                'tipo' => 'operacional',
                'status' => 'ativo',
                'ultimo_acesso' => now(),
            ]
        );
    }
}
