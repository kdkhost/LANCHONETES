<?php

namespace Database\Seeders;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Loja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $lojas = Loja::with('categorias')->get();
        if ($lojas->isEmpty()) {
            return;
        }

        foreach ($lojas as $loja) {
            $perfil = $this->identificarPerfil($loja->slug);
            $categorias = $loja->categorias->keyBy('nome');
            $produtos = $this->catalogoPorPerfil($perfil);

            foreach ($produtos as $dados) {
                $categoria = $categorias->get($dados['categoria']);
                if (!$categoria) {
                    continue;
                }

                $produto = Produto::create([
                    'loja_id'           => $loja->id,
                    'categoria_id'      => $categoria->id,
                    'nome'              => $dados['nome'],
                    'slug'              => Str::slug($loja->slug . '-' . $dados['nome']),
                    'descricao'         => $dados['descricao'],
                    'preco'             => $dados['preco'],
                    'preco_promocional' => $dados['promocional'] ?? null,
                    'destaque'          => $dados['destaque'] ?? false,
                    'novo'              => $dados['novo'] ?? false,
                    'ordem'             => $dados['ordem'] ?? 0,
                    'imagem_principal'  => $dados['imagem'] ?? null,
                    'ativo'             => true,
                    'disponivel'        => true,
                    'tempo_preparo_min' => $dados['tempo'] ?? 18,
                ]);

                if (!empty($dados['grupos'])) {
                    foreach ($dados['grupos'] as $grupo) {
                        $grupoCriado = $produto->gruposAdicionais()->create([
                            'nome'        => $grupo['nome'],
                            'obrigatorio' => $grupo['obrigatorio'] ?? false,
                            'min_selecao' => $grupo['min'] ?? 0,
                            'max_selecao' => $grupo['max'] ?? 3,
                            'ordem'       => $grupo['ordem'] ?? 1,
                        ]);

                        foreach ($grupo['opcoes'] as $index => $opcao) {
                            $grupoCriado->adicionais()->create([
                                'nome'  => $opcao['nome'],
                                'preco' => $opcao['preco'] ?? 0,
                                'ordem' => $index + 1,
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function identificarPerfil(string $slug): string
    {
        return match (true) {
            str_contains($slug, 'pizza')  => 'pizzaria',
            str_contains($slug, 'veggie') => 'veggie',
            default => 'burgers',
        };
    }

    private function catalogoPorPerfil(string $perfil): array
    {
        return match ($perfil) {
            'pizzaria' => $this->catalogoPizzaria(),
            'veggie'   => $this->catalogoVeggie(),
            default    => $this->catalogoBurgers(),
        };
    }

    private function catalogoBurgers(): array
    {
        return [
            [
                'categoria' => 'Lanches Especiais',
                'nome'      => 'Smash Paulista',
                'descricao' => 'Blend 120g smash, queijo cheddar duplo, cebola caramelizada e aioli da casa.',
                'preco'     => 29.90,
                'imagem'    => 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=640&q=80',
                'destaque'  => true,
                'ordem'     => 1,
                'grupos'    => [
                    [
                        'nome' => 'Ponto da carne',
                        'obrigatorio' => true,
                        'min' => 1,
                        'max' => 1,
                        'opcoes' => [
                            ['nome' => 'Mal passado'],
                            ['nome' => 'Ao ponto'],
                            ['nome' => 'Bem passado'],
                        ],
                    ],
                    [
                        'nome' => 'Adicionais',
                        'max'  => 4,
                        'opcoes'=> [
                            ['nome' => 'Cheddar extra', 'preco' => 3.50],
                            ['nome' => 'Bacon crocante', 'preco' => 4.90],
                            ['nome' => 'Picles artesanal', 'preco' => 2.50],
                            ['nome' => 'Maionese defumada', 'preco' => 1.90],
                        ],
                    ],
                ],
            ],
            [
                'categoria' => 'Lanches Especiais',
                'nome'      => 'Brisket BBQ',
                'descricao' => 'Brisket defumado 14h, queijo gouda, coleslaw e molho barbecue artesanal.',
                'preco'     => 36.00,
                'imagem'    => 'https://images.unsplash.com/photo-1508739826987-b79cd8b7da12?auto=format&fit=crop&w=640&q=80',
                'ordem'     => 2,
                'novo'      => true,
            ],
            [
                'categoria' => 'Combos',
                'nome'      => 'Combo Experience',
                'descricao' => 'Escolha qualquer burger + batata artesanal + refrigerante lata.',
                'preco'     => 48.00,
                'promocional'=> 44.00,
                'imagem'    => 'https://images.unsplash.com/photo-1504753793650-d4a2b783c15f?auto=format&fit=crop&w=640&q=80',
                'destaque'  => true,
                'grupos'    => [
                    [
                        'nome' => 'Escolha seu burger',
                        'obrigatorio' => true,
                        'min' => 1,
                        'max' => 1,
                        'opcoes' => [
                            ['nome' => 'Smash Paulista'],
                            ['nome' => 'Brisket BBQ'],
                            ['nome' => 'Clássico X-Burger'],
                        ],
                    ],
                    [
                        'nome' => 'Bebida',
                        'obrigatorio' => true,
                        'min' => 1,
                        'max' => 1,
                        'opcoes' => [
                            ['nome' => 'Refrigerante lata'],
                            ['nome' => 'Suco natural +R$2', 'preco' => 2.00],
                            ['nome' => 'Milkshake +R$8', 'preco' => 8.00],
                        ],
                    ],
                ],
            ],
            [
                'categoria' => 'Porções',
                'nome'      => 'Batata trufada',
                'descricao' => 'Batata rústica com parmesão e óleo trufado.',
                'preco'     => 26.90,
                'imagem'    => 'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?auto=format&fit=crop&w=640&q=80',
                'ordem'     => 4,
            ],
            [
                'categoria' => 'Bebidas',
                'nome'      => 'Milkshake de doce de leite',
                'preco'     => 18.90,
                'imagem'    => 'https://images.unsplash.com/photo-1455156218388-5e61b5268181?auto=format&fit=crop&w=640&q=80',
                'ordem'     => 5,
            ],
            [
                'categoria' => 'Sobremesas',
                'nome'      => 'Cheesecake com frutas vermelhas',
                'preco'     => 19.50,
                'imagem'    => 'https://images.unsplash.com/photo-1461009209120-103fed588fc1?auto=format&fit=crop&w=640&q=80',
                'ordem'     => 6,
            ],
        ];
    }

    private function catalogoPizzaria(): array
    {
        return [
            [
                'categoria' => 'Pizzas Salgadas',
                'nome'      => 'Margherita DOC',
                'descricao' => 'Massa napolitana, molho San Marzano, fior di latte e manjericão fresco.',
                'preco'     => 49.90,
                'imagem'    => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?auto=format&fit=crop&w=640&q=80',
                'destaque'  => true,
                'grupos'    => [
                    [
                        'nome' => 'Tamanho', 'obrigatorio' => true, 'min' => 1, 'max' => 1,
                        'opcoes' => [
                            ['nome' => 'Individual (4 pedaços)'],
                            ['nome' => 'Grande (8 pedaços)', 'preco' => 15.00],
                        ],
                    ],
                    [
                        'nome' => 'Bordas recheadas', 'max' => 1,
                        'opcoes' => [
                            ['nome' => 'Catupiry', 'preco' => 6.00],
                            ['nome' => 'Cheddar', 'preco' => 6.00],
                        ],
                    ],
                ],
            ],
            [
                'categoria' => 'Pizzas Doces',
                'nome'      => 'Nutella com morangos',
                'descricao' => 'Base crocante, creme de avelã e morangos frescos.',
                'preco'     => 42.00,
                'imagem'    => 'https://images.unsplash.com/photo-1523986371872-9d3ba2e2f5ab?auto=format&fit=crop&w=640&q=80',
                'novo'      => true,
            ],
            [
                'categoria' => 'Massas Artesanais',
                'nome'      => 'Ravioli de burrata',
                'descricao' => 'Recheio de burrata com pesto de pistache.',
                'preco'     => 54.90,
                'imagem'    => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=640&q=80',
            ],
            [
                'categoria' => 'Bebidas Premium',
                'nome'      => 'Spritz artesanal',
                'preco'     => 24.90,
                'imagem'    => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=640&q=80',
            ],
        ];
    }

    private function catalogoVeggie(): array
    {
        return [
            [
                'categoria' => 'Bowls Autorais',
                'nome'      => 'Bowl Mediterrâneo',
                'descricao' => 'Quinoa orgânica, homus defumado, falafel e legumes grelhados.',
                'preco'     => 32.90,
                'imagem'    => 'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?auto=format&fit=crop&w=640&q=80',
                'destaque'  => true,
                'grupos'    => [
                    [
                        'nome' => 'Proteína vegetal',
                        'obrigatorio' => true,
                        'opcoes' => [
                            ['nome' => 'Falafel'],
                            ['nome' => 'Tofu grelhado'],
                            ['nome' => 'Cogumelos shimeji'],
                        ],
                    ],
                ],
            ],
            [
                'categoria' => 'Saladas Quentes',
                'nome'      => 'Salada Thai',
                'descricao' => 'Mix de folhas, manga, pepino, castanhas e molho agridoce.',
                'preco'     => 27.50,
                'imagem'    => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&w=640&q=80',
            ],
            [
                'categoria' => 'Drinks Naturais',
                'nome'      => 'Kombucha artesanal',
                'preco'     => 15.90,
                'imagem'    => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=640&q=80',
            ],
            [
                'categoria' => 'Sobremesas Veganas',
                'nome'      => 'Mousse de cacau com castanhas',
                'preco'     => 18.00,
                'imagem'    => 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?auto=format&fit=crop&w=640&q=80',
            ],
        ];
    }
}
