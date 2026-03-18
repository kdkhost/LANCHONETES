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
        $lojas = Loja::all();

        Usuario::create([
            'nome'      => 'Administrador Master',
            'email'     => 'admin@lanchonete.com',
            'telefone'  => '(11) 99999-0001',
            'whatsapp'  => '11999990001',
            'senha'     => 'admin123',
            'role'      => 'super_admin',
            'ativo'     => true,
        ]);

        foreach ($lojas as $index => $loja) {
            $num = $index + 1;

            $admin = Usuario::create([
                'nome'      => "Admin {$loja->nome}",
                'email'     => "admin{$num}@demo.com",
                'telefone'  => '(11) 90000-00' . $num,
                'whatsapp'  => '119000000' . $num,
                'senha'     => 'admin123',
                'role'      => 'admin',
                'loja_id'   => $loja->id,
                'ativo'     => true,
            ]);

            $entregador = Usuario::create([
                'nome'      => "Entregador {$loja->nome}",
                'email'     => "entregador{$num}@demo.com",
                'telefone'  => '(11) 95555-00' . $num,
                'whatsapp'  => '119555500' . $num,
                'senha'     => 'entregador123',
                'role'      => 'entregador',
                'loja_id'   => $loja->id,
                'ativo'     => true,
            ]);

            Funcionario::create([
                'usuario_id'          => $entregador->id,
                'loja_id'             => $loja->id,
                'tipo'                => 'funcionario',
                'cargo'               => 'Entregador',
                'e_entregador'        => true,
                'veiculo'             => 'Moto Yamaha NMax ' . (120 + $num),
                'placa_veiculo'       => 'DEM-' . str_pad($num, 3, '0', STR_PAD_LEFT),
                'disponivel_entregas' => true,
                'data_admissao'       => now()->subDays($num * 10)->toDateString(),
                'ativo'               => true,
            ]);

            Usuario::create([
                'nome'      => "Cliente Demo {$loja->cidade}",
                'email'     => "cliente{$num}@demo.com",
                'telefone'  => '(11) 94444-00' . $num,
                'whatsapp'  => '119444400' . $num,
                'senha'     => 'cliente123',
                'role'      => 'cliente',
                'loja_id'   => $loja->id,
                'ativo'     => true,
            ]);
        }
    }
}
