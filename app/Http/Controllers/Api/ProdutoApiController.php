<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\Produto;

class ProdutoApiController extends Controller
{
    public function porLoja(string $lojaSlug)
    {
        $loja = Loja::where('slug', $lojaSlug)->firstOrFail();
        $produtos = Produto::with(['categoria', 'gruposAdicionais.adicionais'])
            ->where('loja_id', $loja->id)
            ->where('ativo', true)
            ->where('disponivel', true)
            ->orderBy('ordem')
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'nome'          => $p->nome,
                'slug'          => $p->slug,
                'descricao'     => $p->descricao,
                'preco'         => $p->preco,
                'preco_atual'   => $p->preco_atual,
                'tem_promocao'  => $p->tem_promocao,
                'imagem_url'    => $p->imagem_url,
                'categoria_id'  => $p->categoria_id,
                'destaque'      => (bool) $p->destaque,
                'novo'          => (bool) $p->novo,
                'grupos'        => $p->gruposAdicionais->map(fn($g) => [
                    'id'          => $g->id,
                    'nome'        => $g->nome,
                    'obrigatorio' => (bool) $g->obrigatorio,
                    'min'         => $g->min_selecao,
                    'max'         => $g->max_selecao,
                    'adicionais'  => $g->adicionais->map(fn($a) => [
                        'id'    => $a->id,
                        'nome'  => $a->nome,
                        'preco' => $a->preco,
                    ]),
                ]),
            ]);

        return response()->json(['produtos' => $produtos, 'loja' => ['id' => $loja->id, 'nome' => $loja->nome]]);
    }

    public function show(int $id)
    {
        $produto = Produto::with(['gruposAdicionais.adicionais'])
            ->where('ativo', true)
            ->findOrFail($id);

        return response()->json([
            'produto' => [
                'id'           => $produto->id,
                'nome'         => $produto->nome,
                'descricao'    => $produto->descricao,
                'preco'        => $produto->preco,
                'preco_atual'  => $produto->preco_atual,
                'imagem_url'   => $produto->imagem_url,
                'tem_promocao' => $produto->tem_promocao,
                'grupos'       => $produto->gruposAdicionais->map(fn($g) => [
                    'id'          => $g->id,
                    'nome'        => $g->nome,
                    'obrigatorio' => (bool) $g->obrigatorio,
                    'min'         => $g->min_selecao,
                    'max'         => $g->max_selecao,
                    'adicionais'  => $g->adicionais->map(fn($a) => ['id' => $a->id, 'nome' => $a->nome, 'preco' => $a->preco]),
                ]),
            ],
        ]);
    }
}
