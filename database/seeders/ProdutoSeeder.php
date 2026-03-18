<?php

namespace Database\Seeders;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Loja;
use App\Models\GrupoAdicional;
use App\Models\Adicional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $loja   = Loja::first();
        if (!$loja) return;

        $catLanches    = Categoria::where('loja_id', $loja->id)->where('nome', 'Lanches')->first();
        $catBebidas    = Categoria::where('loja_id', $loja->id)->where('nome', 'Bebidas')->first();
        $catPorcoes    = Categoria::where('loja_id', $loja->id)->where('nome', 'Porções')->first();
        $catSobremesas = Categoria::where('loja_id', $loja->id)->where('nome', 'Sobremesas')->first();
        $catCombos     = Categoria::where('loja_id', $loja->id)->where('nome', 'Combos')->first();

        $lanches = [
            ['nome' => 'X-Burguer',     'descricao' => 'Pão, hambúrguer artesanal 150g, queijo, alface, tomate, maionese caseira.',    'preco' => 22.90, 'destaque' => true,  'novo' => false, 'ordem' => 1],
            ['nome' => 'X-Bacon',       'descricao' => 'Pão, hambúrguer 150g, bacon crocante, queijo cheddar, cebola crispy.',          'preco' => 26.90, 'destaque' => true,  'novo' => false, 'ordem' => 2],
            ['nome' => 'X-Frango',      'descricao' => 'Pão, filé de frango grelhado, queijo prato, alface americana, tomate.',         'preco' => 24.90, 'destaque' => false, 'novo' => false, 'ordem' => 3],
            ['nome' => 'X-Tudo',        'descricao' => 'Pão, hambúrguer duplo, bacon, queijo, alface, tomate, ovo, milho, batata palha.','preco' => 32.90, 'destaque' => true,  'novo' => true,  'ordem' => 4],
            ['nome' => 'X-Veggie',      'descricao' => 'Pão integral, hambúrguer de grão-de-bico, queijo, rúcula, tomate seco.',         'preco' => 24.90, 'destaque' => false, 'novo' => true,  'ordem' => 5],
        ];

        foreach ($lanches as $l) {
            $produto = Produto::create([
                'loja_id'      => $loja->id,
                'categoria_id' => $catLanches?->id,
                'nome'         => $l['nome'],
                'slug'         => Str::slug($l['nome']),
                'descricao'    => $l['descricao'],
                'preco'        => $l['preco'],
                'destaque'     => $l['destaque'],
                'novo'         => $l['novo'],
                'ordem'        => $l['ordem'],
                'ativo'        => true,
                'disponivel'   => true,
                'tempo_preparo_min' => 15,
            ]);

            // Grupo: Ponto do hambúrguer
            if (in_array($l['nome'], ['X-Burguer', 'X-Bacon', 'X-Tudo'])) {
                $grupoPonto = $produto->gruposAdicionais()->create([
                    'nome'        => 'Ponto do Hambúrguer',
                    'obrigatorio' => true,
                    'min_selecao' => 1,
                    'max_selecao' => 1,
                    'ordem'       => 1,
                ]);
                foreach (['Mal passado', 'Ao ponto', 'Bem passado'] as $i => $ponto) {
                    $grupoPonto->adicionais()->create(['nome' => $ponto, 'preco' => 0, 'ordem' => $i + 1]);
                }
            }

            // Grupo: Adicionais
            $grupoAdic = $produto->gruposAdicionais()->create([
                'nome'        => 'Adicionais',
                'obrigatorio' => false,
                'min_selecao' => 0,
                'max_selecao' => 5,
                'ordem'       => 2,
            ]);
            $adicionais = [
                ['nome' => 'Queijo extra',    'preco' => 2.00],
                ['nome' => 'Bacon extra',     'preco' => 3.00],
                ['nome' => 'Ovo frito',       'preco' => 2.00],
                ['nome' => 'Milho',           'preco' => 1.00],
                ['nome' => 'Batata palha',    'preco' => 1.50],
                ['nome' => 'Cebola crispy',   'preco' => 2.00],
            ];
            foreach ($adicionais as $i => $a) {
                $grupoAdic->adicionais()->create(array_merge($a, ['ordem' => $i + 1]));
            }
        }

        // Bebidas
        if ($catBebidas) {
            $bebidas = [
                ['nome' => 'Refrigerante Lata',  'preco' => 5.00,  'ordem' => 1],
                ['nome' => 'Suco Natural 300ml', 'preco' => 9.00,  'ordem' => 2],
                ['nome' => 'Água Mineral',       'preco' => 3.00,  'ordem' => 3],
                ['nome' => 'Milkshake 400ml',    'preco' => 14.90, 'ordem' => 4],
                ['nome' => 'Vitamina de Frutas', 'preco' => 12.00, 'ordem' => 5],
            ];
            foreach ($bebidas as $b) {
                Produto::create([
                    'loja_id'      => $loja->id,
                    'categoria_id' => $catBebidas->id,
                    'nome'         => $b['nome'],
                    'slug'         => Str::slug($b['nome']),
                    'preco'        => $b['preco'],
                    'ordem'        => $b['ordem'],
                    'ativo'        => true,
                    'disponivel'   => true,
                ]);
            }
        }

        // Porções
        if ($catPorcoes) {
            $porcoes = [
                ['nome' => 'Batata Frita P',        'preco' => 16.90, 'ordem' => 1],
                ['nome' => 'Batata Frita M',        'preco' => 22.90, 'ordem' => 2],
                ['nome' => 'Batata Frita G',        'preco' => 28.90, 'ordem' => 3],
                ['nome' => 'Onion Rings',           'preco' => 22.90, 'ordem' => 4],
                ['nome' => 'Nuggets 10 peças',      'preco' => 19.90, 'ordem' => 5],
            ];
            foreach ($porcoes as $p) {
                Produto::create([
                    'loja_id'      => $loja->id,
                    'categoria_id' => $catPorcoes->id,
                    'nome'         => $p['nome'],
                    'slug'         => Str::slug($p['nome']),
                    'preco'        => $p['preco'],
                    'ordem'        => $p['ordem'],
                    'ativo'        => true,
                    'disponivel'   => true,
                    'tempo_preparo_min' => 12,
                ]);
            }
        }

        // Combos
        if ($catCombos) {
            Produto::create([
                'loja_id'           => $loja->id,
                'categoria_id'      => $catCombos->id,
                'nome'              => 'Combo X-Burguer',
                'slug'              => 'combo-x-burguer',
                'descricao'         => 'X-Burguer + Batata Frita M + Refrigerante Lata. Economia de R$ 5,00!',
                'preco'             => 34.90,
                'preco_promocional' => 32.90,
                'destaque'          => true,
                'novo'              => false,
                'ordem'             => 1,
                'ativo'             => true,
                'disponivel'        => true,
                'tempo_preparo_min' => 20,
            ]);
        }
    }
}
