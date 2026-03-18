<?php

namespace Database\Seeders;

use App\Models\ItemPedido;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $lojas = Loja::all();
        if ($lojas->isEmpty()) {
            return;
        }

        foreach ($lojas as $loja) {
            $cliente = Usuario::where('loja_id', $loja->id)->where('role', 'cliente')->first();
            if (!$cliente) {
                continue;
            }

            $produtos = Produto::where('loja_id', $loja->id)->get();
            if ($produtos->isEmpty()) {
                continue;
            }

            $statusFlow = ['entregue', 'confirmado'];

            foreach ($statusFlow as $index => $status) {
                $itensSelecionados = $produtos->shuffle()->take(min(3, $produtos->count()));
                $subtotal = 0;
                $taxaEntrega = 6.90;
                $momento = now()->subDays($index + 1);

                $pedido = Pedido::create([
                    'loja_id'            => $loja->id,
                    'usuario_id'         => $cliente->id,
                    'status'             => $status,
                    'tipo_entrega'       => 'entrega',
                    'endereco_logradouro'=> $loja->logradouro,
                    'endereco_numero'    => $loja->numero,
                    'endereco_bairro'    => $loja->bairro,
                    'endereco_cidade'    => $loja->cidade,
                    'endereco_estado'    => $loja->estado,
                    'endereco_cep'       => $loja->cep,
                    'taxa_entrega'       => $taxaEntrega,
                    'tempo_estimado_min' => 35,
                    'confirmado_em'      => $momento->copy()->addMinutes(10),
                    'entregue_em'        => $status === 'entregue' ? $momento->copy()->addMinutes(50) : null,
                ]);

                foreach ($itensSelecionados as $produto) {
                    $quantidade = rand(1, 2);
                    $subtotalItem = $produto->preco * $quantidade;
                    $subtotal += $subtotalItem;

                    ItemPedido::create([
                        'pedido_id'        => $pedido->id,
                        'produto_id'       => $produto->id,
                        'produto_nome'     => $produto->nome,
                        'produto_descricao'=> $produto->descricao,
                        'produto_preco'    => $produto->preco,
                        'quantidade'       => $quantidade,
                        'subtotal'         => $subtotalItem,
                    ]);
                }

                $pedido->update([
                    'subtotal' => $subtotal,
                    'desconto' => 0,
                    'total'    => $subtotal + $taxaEntrega,
                ]);
            }
        }
    }
}
