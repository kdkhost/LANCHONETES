<?php

namespace Database\Seeders;

use App\Models\Avaliacao;
use App\Models\Loja;
use App\Models\Pedido;
use Illuminate\Database\Seeder;

class AvaliacaoSeeder extends Seeder
{
    public function run(): void
    {
        $lojas = Loja::all();
        foreach ($lojas as $loja) {
            $pedidos = Pedido::where('loja_id', $loja->id)->where('status', 'entregue')->get();
            foreach ($pedidos as $pedido) {
                Avaliacao::create([
                    'pedido_id'    => $pedido->id,
                    'usuario_id'   => $pedido->usuario_id,
                    'loja_id'      => $loja->id,
                    'nota_loja'    => rand(4, 5),
                    'nota_entrega' => rand(4, 5),
                    'nota_comida'  => rand(4, 5),
                    'comentario'   => fake()->randomElement([
                        'Entrega rápida e comida impecável!',
                        'Cliente elogiou embalagens sustentáveis e sabor único.',
                        'Atendimento incrível, recomendo para toda a rede.',
                    ]),
                    'aprovado'     => true,
                ]);
            }
        }
    }
}
