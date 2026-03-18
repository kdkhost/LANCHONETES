<?php

namespace Database\Seeders;

use App\Models\Loja;
use App\Models\BairroEntrega;
use Illuminate\Database\Seeder;

class LojaSeeder extends Seeder
{
    public function run(): void
    {
        $lojas = [
            [
                'nome'        => 'Lanchonete do Zé',
                'slug'        => 'lanchonete-do-ze',
                'descricao'   => 'Hambúrgueres smash, maionese da casa e delivery relâmpago no centro de São Paulo.',
                'telefone'    => '(11) 98765-4321',
                'whatsapp'    => '11987654321',
                'email'       => 'contato@lanchonetedoze.com',
                'logo'        => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=320&q=80',
                'banner'      => 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=1200&q=80',
                'cidade'      => 'São Paulo',
                'estado'      => 'SP',
                'bairro'      => 'Bela Vista',
                'logradouro'  => 'Av. Paulista',
                'numero'      => '1000',
                'cep'         => '01310-100',
                'cor_primaria'=> '#FF6B35',
                'cor_secundaria'=> '#2C3E50',
            ],
            [
                'nome'        => 'Casa das Pizzas Artesanais',
                'slug'        => 'casa-das-pizzas',
                'descricao'   => 'Massa de longa fermentação, forno a lenha e sabores autorais premiados.',
                'telefone'    => '(21) 98888-1234',
                'whatsapp'    => '21988881234',
                'email'       => 'contato@casadaspizzas.com',
                'logo'        => 'https://images.unsplash.com/photo-1548365328-9ad5d86f9a22?auto=format&fit=crop&w=320&q=80',
                'banner'      => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?auto=format&fit=crop&w=1200&q=80',
                'cidade'      => 'Rio de Janeiro',
                'estado'      => 'RJ',
                'bairro'      => 'Botafogo',
                'logradouro'  => 'Rua Conde de Irajá',
                'numero'      => '250',
                'cep'         => '22271-020',
                'cor_primaria'=> '#D64541',
                'cor_secundaria'=> '#1F2A44',
            ],
            [
                'nome'        => 'Veggie Garden Delivery',
                'slug'        => 'veggie-garden',
                'descricao'   => 'Bowls plant-based e saladas autorais com ingredientes rastreados.',
                'telefone'    => '(41) 97777-9988',
                'whatsapp'    => '41977779988',
                'email'       => 'contato@veggiegarden.com',
                'logo'        => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=320&q=80',
                'banner'      => 'https://images.unsplash.com/photo-1506089676908-3592f7389d4d?auto=format&fit=crop&w=1200&q=80',
                'cidade'      => 'Curitiba',
                'estado'      => 'PR',
                'bairro'      => 'Batel',
                'logradouro'  => 'Av. do Batel',
                'numero'      => '1500',
                'cep'         => '80420-090',
                'cor_primaria'=> '#0FA958',
                'cor_secundaria'=> '#0F2231',
            ],
        ];

        foreach ($lojas as $dados) {
            $loja = Loja::create(array_merge($dados, [
                'aceita_retirada'          => true,
                'aceita_entrega'           => true,
                'aceita_pagamento_entrega' => true,
                'tipo_taxa_entrega'        => 'bairro',
                'taxa_entrega_fixa'        => 6.90,
                'raio_entrega_km'          => 12,
                'pedido_minimo'            => 25,
                'tempo_entrega_min'        => 25,
                'tempo_entrega_max'        => 50,
                'horarios_funcionamento'   => [
                    'segunda' => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                    'terca'   => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                    'quarta'  => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                    'quinta'  => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                    'sexta'   => ['abre' => '11:00', 'fecha' => '00:30', 'ativo' => true],
                    'sabado'  => ['abre' => '11:00', 'fecha' => '01:00', 'ativo' => true],
                    'domingo' => ['abre' => '12:00', 'fecha' => '23:00', 'ativo' => true],
                ],
            ]));

            $bairros = [
                ['nome' => 'Centro',        'cidade' => $loja->cidade, 'estado' => $loja->estado, 'taxa' => 4.90],
                ['nome' => 'Zona Sul',      'cidade' => $loja->cidade, 'estado' => $loja->estado, 'taxa' => 5.90],
                ['nome' => 'Zona Norte',    'cidade' => $loja->cidade, 'estado' => $loja->estado, 'taxa' => 6.90],
                ['nome' => 'Zona Oeste',    'cidade' => $loja->cidade, 'estado' => $loja->estado, 'taxa' => 7.90],
            ];

            foreach ($bairros as $idx => $bairro) {
                BairroEntrega::create(array_merge($bairro, [
                    'loja_id' => $loja->id,
                    'tempo_estimado_min' => 25 + ($idx * 5),
                    'tempo_estimado_max' => 40 + ($idx * 5),
                    'ativo' => true,
                ]));
            }
        }
    }
}
