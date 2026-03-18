<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Loja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $lojas = Loja::all();
        if ($lojas->isEmpty()) {
            return;
        }

        $templates = [
            'burgers' => [
                ['nome' => 'Lanches Especiais', 'icone' => 'burger', 'ordem' => 1, 'destaque' => true],
                ['nome' => 'Combos',            'icone' => 'bag-check', 'ordem' => 2, 'destaque' => true],
                ['nome' => 'Porções',           'icone' => 'box-seam', 'ordem' => 3],
                ['nome' => 'Bebidas',           'icone' => 'cup-straw', 'ordem' => 4],
                ['nome' => 'Sobremesas',        'icone' => 'cake2', 'ordem' => 5],
                ['nome' => 'Promoções',         'icone' => 'tag', 'ordem' => 6, 'destaque' => true],
            ],
            'pizzaria' => [
                ['nome' => 'Pizzas Salgadas',   'icone' => 'pizza', 'ordem' => 1, 'destaque' => true],
                ['nome' => 'Pizzas Doces',      'icone' => 'heart', 'ordem' => 2],
                ['nome' => 'Massas Artesanais', 'icone' => 'egg-fried', 'ordem' => 3],
                ['nome' => 'Bebidas Premium',   'icone' => 'wine', 'ordem' => 4],
            ],
            'veggie' => [
                ['nome' => 'Bowls Autorais',      'icone' => 'flower1', 'ordem' => 1, 'destaque' => true],
                ['nome' => 'Saladas Quentes',     'icone' => 'sun', 'ordem' => 2],
                ['nome' => 'Drinks Naturais',     'icone' => 'droplet', 'ordem' => 3],
                ['nome' => 'Sobremesas Veganas',  'icone' => 'cupcake', 'ordem' => 4],
            ],
        ];

        foreach ($lojas as $loja) {
            $perfil = 'burgers';
            if (str_contains($loja->slug, 'pizza')) {
                $perfil = 'pizzaria';
            } elseif (str_contains($loja->slug, 'veggie')) {
                $perfil = 'veggie';
            }

            foreach ($templates[$perfil] as $dados) {
                Categoria::create([
                    'loja_id'  => $loja->id,
                    'categoria_pai_id' => null,
                    'nome'     => $dados['nome'],
                    'slug'     => Str::slug($dados['nome']),
                    'icone'    => $dados['icone'],
                    'ordem'    => $dados['ordem'],
                    'ativo'    => true,
                    'destaque' => $dados['destaque'] ?? false,
                ]);
            }
        }
    }
}
