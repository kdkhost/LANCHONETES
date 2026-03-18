<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Funcionario;
use App\Models\Loja;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $loja = Loja::first();

        // Super Admin
        Usuario::create([
            'nome'      => 'Administrador',
            'email'     => 'admin@lanchonete.com',
            'telefone'  => '(11) 99999-0001',
            'whatsapp'  => '11999990001',
            'senha'     => 'admin123',
            'role'      => 'super_admin',
            'loja_id'   => $loja?->id,
            'ativo'     => true,
        ]);

        // Gerente
        $gerente = Usuario::create([
            'nome'      => 'Maria Gerente',
            'email'     => 'gerente@lanchonete.com',
            'telefone'  => '(11) 99999-0002',
            'whatsapp'  => '11999990002',
            'senha'     => 'gerente123',
            'role'      => 'gerente',
            'loja_id'   => $loja?->id,
            'ativo'     => true,
        ]);

        // Atendente
        $atendente = Usuario::create([
            'nome'      => 'João Atendente',
            'email'     => 'atendente@lanchonete.com',
            'telefone'  => '(11) 99999-0003',
            'whatsapp'  => '11999990003',
            'senha'     => 'atendente123',
            'role'      => 'atendente',
            'loja_id'   => $loja?->id,
            'ativo'     => true,
        ]);

        Funcionario::create([
            'usuario_id'   => $atendente->id,
            'loja_id'      => $loja?->id,
            'tipo'         => 'funcionario',
            'cargo'        => 'Atendente',
            'e_entregador' => false,
            'data_admissao'=> now()->toDateString(),
            'ativo'        => true,
        ]);

        // Entregador
        $entregador = Usuario::create([
            'nome'      => 'Carlos Entregador',
            'email'     => 'entregador@lanchonete.com',
            'telefone'  => '(11) 99999-0004',
            'whatsapp'  => '11999990004',
            'senha'     => 'entregador123',
            'role'      => 'entregador',
            'loja_id'   => $loja?->id,
            'ativo'     => true,
        ]);

        Funcionario::create([
            'usuario_id'          => $entregador->id,
            'loja_id'             => $loja?->id,
            'tipo'                => 'funcionario',
            'cargo'               => 'Entregador',
            'e_entregador'        => true,
            'veiculo'             => 'Moto Honda CG 160',
            'placa_veiculo'       => 'ABC-1234',
            'disponivel_entregas' => true,
            'data_admissao'       => now()->toDateString(),
            'ativo'               => true,
        ]);

        // Cliente de exemplo
        Usuario::create([
            'nome'      => 'Cliente Teste',
            'email'     => 'cliente@lanchonete.com',
            'telefone'  => '(11) 99999-0005',
            'whatsapp'  => '11999990005',
            'senha'     => 'cliente123',
            'role'      => 'cliente',
            'ativo'     => true,
        ]);
    }
}
