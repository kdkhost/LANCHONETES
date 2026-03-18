<?php

namespace Database\Seeders;

use App\Models\Loja;
use App\Models\BairroEntrega;
use Illuminate\Database\Seeder;

class LojaSeeder extends Seeder
{
    public function run(): void
    {
        $loja = Loja::create([
            'nome'                     => 'Lanchonete do Zé',
            'slug'                     => 'lanchonete-do-ze',
            'cnpj'                     => '00.000.000/0001-00',
            'descricao'                => 'Os melhores lanches da cidade, feitos com ingredientes frescos!',
            'telefone'                 => '(11) 98765-4321',
            'whatsapp'                 => '11987654321',
            'email'                    => 'contato@lanchonete.com',
            'cep'                      => '01310-100',
            'logradouro'               => 'Avenida Paulista',
            'numero'                   => '1000',
            'complemento'              => 'Loja 1',
            'bairro'                   => 'Bela Vista',
            'cidade'                   => 'São Paulo',
            'estado'                   => 'SP',
            'latitude'                 => -23.5614,
            'longitude'                => -46.6558,
            'cor_primaria'             => '#FF6B35',
            'cor_secundaria'           => '#2C3E50',
            'ativo'                    => true,
            'aceita_retirada'          => true,
            'aceita_entrega'           => true,
            'aceita_pagamento_entrega' => true,
            'pedido_minimo'            => 15.00,
            'tempo_entrega_min'        => 25,
            'tempo_entrega_max'        => 45,
            'tipo_taxa_entrega'        => 'bairro',
            'taxa_entrega_fixa'        => 5.00,
            'raio_entrega_km'          => 10,
            'notificacoes_whatsapp'    => false,
            'horarios_funcionamento'   => [
                'segunda'  => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                'terca'    => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                'quarta'   => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                'quinta'   => ['abre' => '11:00', 'fecha' => '23:00', 'ativo' => true],
                'sexta'    => ['abre' => '11:00', 'fecha' => '00:00', 'ativo' => true],
                'sabado'   => ['abre' => '11:00', 'fecha' => '01:00', 'ativo' => true],
                'domingo'  => ['abre' => '12:00', 'fecha' => '22:00', 'ativo' => true],
            ],
        ]);

        $bairros = [
            ['nome' => 'Bela Vista',     'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 4.00,  'tempo_estimado_min' => 20, 'tempo_estimado_max' => 35],
            ['nome' => 'Consolação',     'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 4.00,  'tempo_estimado_min' => 20, 'tempo_estimado_max' => 35],
            ['nome' => 'Jardins',        'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 5.00,  'tempo_estimado_min' => 25, 'tempo_estimado_max' => 40],
            ['nome' => 'Pinheiros',      'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 6.00,  'tempo_estimado_min' => 30, 'tempo_estimado_max' => 45],
            ['nome' => 'Itaim Bibi',     'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 6.00,  'tempo_estimado_min' => 30, 'tempo_estimado_max' => 45],
            ['nome' => 'Vila Mariana',   'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 5.50,  'tempo_estimado_min' => 25, 'tempo_estimado_max' => 40],
            ['nome' => 'Centro',         'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 3.50,  'tempo_estimado_min' => 15, 'tempo_estimado_max' => 30],
            ['nome' => 'Paraíso',        'cidade' => 'São Paulo', 'estado' => 'SP', 'taxa' => 4.50,  'tempo_estimado_min' => 20, 'tempo_estimado_max' => 35],
        ];

        foreach ($bairros as $bairro) {
            BairroEntrega::create(array_merge($bairro, ['loja_id' => $loja->id, 'ativo' => true]));
        }
    }
}
