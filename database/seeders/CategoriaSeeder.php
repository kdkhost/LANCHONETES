<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Loja;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $loja = Loja::first();
        if (!$loja) return;

        $categorias = [
            ['nome' => 'Lanches',        'icone' => 'burger',          'ordem' => 1],
            ['nome' => 'Pizzas',         'icone' => 'circle',          'ordem' => 2],
            ['nome' => 'Porções',        'icone' => 'box-seam',        'ordem' => 3],
            ['nome' => 'Bebidas',        'icone' => 'cup-straw',       'ordem' => 4],
            ['nome' => 'Sobremesas',     'icone' => 'cake2',           'ordem' => 5],
            ['nome' => 'Combos',         'icone' => 'bag-check',       'ordem' => 6],
            ['nome' => 'Promoções',      'icone' => 'tag',             'ordem' => 7],
        ];

        foreach ($categorias as $dados) {
            Categoria::create(array_merge($dados, [
                'loja_id' => $loja->id,
                'slug'    => \Illuminate\Support\Str::slug($dados['nome']),
                'ativo'   => true,
                'destaque'=> in_array($dados['nome'], ['Lanches', 'Combos', 'Promoções']),
            ]));
        }
    }
}
