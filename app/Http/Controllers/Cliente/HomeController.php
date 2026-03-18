<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\Categoria;
use App\Models\Produto;
use App\Models\Banner;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, ?string $lojaSlug = null)
    {
        $loja = $lojaSlug
            ? Loja::where('slug', $lojaSlug)->where('ativo', true)->firstOrFail()
            : (app()->bound('loja_atual') ? app('loja_atual') : Loja::where('ativo', true)->first());

        if (!$loja) {
            return view('cliente.sem-lojas');
        }

        $banners = Banner::where('loja_id', $loja->id)->where('ativo', true)
            ->orderBy('ordem')->get()->filter(fn($b) => $b->estaAtivo());

        $categorias = Categoria::where('loja_id', $loja->id)
            ->where('ativo', true)
            ->whereNull('categoria_pai_id')
            ->with(['produtos' => fn($q) => $q->where('ativo', true)->where('disponivel', true)->orderBy('ordem')])
            ->orderBy('ordem')
            ->get();

        $destaquesIds = Produto::where('loja_id', $loja->id)
            ->where('ativo', true)->where('disponivel', true)->where('destaque', true)
            ->pluck('id');

        $destaques = $destaquesIds->isNotEmpty()
            ? Produto::whereIn('id', $destaquesIds)->get()
            : collect();

        $estaAberta  = $loja->estaAberta();
        $lojas       = Loja::where('ativo', true)->select('id', 'nome', 'slug', 'logo', 'cidade')->get();

        return view('cliente.home', compact('loja', 'banners', 'categorias', 'destaques', 'estaAberta', 'lojas'));
    }

    public function buscar(Request $request)
    {
        $loja = app('loja_atual');
        $q    = $request->get('q', '');

        $produtos = Produto::where('loja_id', $loja->id)
            ->where('ativo', true)->where('disponivel', true)
            ->where(function ($query) use ($q) {
                $query->where('nome', 'like', "%{$q}%")
                      ->orWhere('descricao', 'like', "%{$q}%")
                      ->orWhere('ingredientes', 'like', "%{$q}%");
            })
            ->with('categoria')
            ->orderBy('ordem')
            ->paginate(12)
            ->appends(['q' => $q]);

        if ($request->expectsJson()) {
            $items = $produtos->getCollection()->map(fn($p) => [
                'id'          => $p->id,
                'nome'        => $p->nome,
                'descricao'   => $p->descricao,
                'preco'       => $p->preco_atual,
                'imagem_url'  => $p->imagem_url,
                'categoria'   => $p->categoria?->nome,
            ]);

            return response()->json([
                'produtos' => $items,
                'meta' => [
                    'pagina_atual' => $produtos->currentPage(),
                    'ultima_pagina'=> $produtos->lastPage(),
                    'total'        => $produtos->total(),
                ],
            ]);
        }

        return view('cliente.busca', compact('produtos', 'q', 'loja'));
    }

    public function produto(string $slug)
    {
        $loja    = app('loja_atual');
        $produto = Produto::where('loja_id', $loja->id)
            ->where('slug', $slug)
            ->where('ativo', true)
            ->with('gruposAdicionais.adicionais')
            ->firstOrFail();

        return view('cliente.produto', compact('produto', 'loja'));
    }

    public function listaLojas()
    {
        $lojas = Loja::where('ativo', true)
            ->withCount(['produtos', 'avaliacoes'])
            ->get();

        return view('cliente.lojas', compact('lojas'));
    }
}
