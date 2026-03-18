<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\GrupoAdicional;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProdutoAdminController extends Controller
{
    public function __construct(private UploadService $uploadService) {}

    public function index(Request $request)
    {
        $lojaId   = Auth::user()->loja_id;
        $produtos = Produto::with('categoria')
            ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->when($request->busca, fn($q) => $q->where('nome', 'like', "%{$request->busca}%"))
            ->when($request->categoria_id, fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->status !== null, fn($q) => $q->where('ativo', $request->status))
            ->orderBy('ordem')
            ->paginate(20);

        $categorias = Categoria::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->where('ativo', true)->get();

        return view('admin.produtos.index', compact('produtos', 'categorias'));
    }

    public function create()
    {
        $lojaId     = Auth::user()->loja_id;
        $categorias = Categoria::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->where('ativo', true)->orderBy('nome')->get();
        $produto = null;
        return view('admin.produtos.form', compact('categorias', 'produto'));
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $lojaId = Auth::user()->loja_id;

        if ($request->hasFile('imagem_principal')) {
            $dados['imagem_principal'] = $this->uploadService->salvarImagemProduto($request->file('imagem_principal'));
        } elseif ($request->filled('imagem_path')) {
            $dados['imagem_principal'] = $request->imagem_path;
        }

        if ($request->hasFile('imagens_extras')) {
            $imagens = [];
            foreach ($request->file('imagens_extras') as $arquivo) {
                $imagens[] = $this->uploadService->salvarImagemProduto($arquivo);
            }
            $dados['imagens'] = $imagens;
        }

        $dados['loja_id'] = $lojaId;
        $dados['slug']    = Str::slug($dados['nome']);
        $produto          = Produto::create($dados);

        if ($request->grupos) {
            foreach ($request->grupos as $grupo) {
                $g = $produto->gruposAdicionais()->create([
                    'nome'         => $grupo['nome'],
                    'min_selecao'  => $grupo['min_selecao'] ?? 0,
                    'max_selecao'  => $grupo['max_selecao'] ?? 1,
                    'obrigatorio'  => isset($grupo['obrigatorio']),
                    'ordem'        => $grupo['ordem'] ?? 0,
                ]);
                foreach ($grupo['adicionais'] ?? [] as $adc) {
                    $g->adicionais()->create([
                        'nome'  => $adc['nome'],
                        'preco' => $adc['preco'] ?? 0,
                        'ordem' => $adc['ordem'] ?? 0,
                    ]);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true, 'produto' => $produto->load('gruposAdicionais.adicionais')]);
        }

        return redirect()->route('admin.produtos.index')->with('sucesso', 'Produto cadastrado com sucesso!');
    }

    public function edit(Produto $produto)
    {
        $lojaId     = Auth::user()->loja_id;
        $categorias = Categoria::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->where('ativo', true)->orderBy('nome')->get();
        $produto->load('gruposAdicionais.adicionais');
        return view('admin.produtos.form', compact('produto', 'categorias'));
    }

    public function update(Request $request, Produto $produto)
    {
        $dados = $this->validar($request, $produto->id);

        if ($request->hasFile('imagem_principal')) {
            $this->uploadService->deletar($produto->imagem_principal);
            $dados['imagem_principal'] = $this->uploadService->salvarImagemProduto($request->file('imagem_principal'));
        } elseif ($request->filled('imagem_path')) {
            $dados['imagem_principal'] = $request->imagem_path;
        }

        $produto->update($dados);

        if ($request->grupos) {
            $idsEnviados = [];
            foreach ($request->grupos as $grupo) {
                if (!empty($grupo['id'])) {
                    $g = $produto->gruposAdicionais()->find($grupo['id']);
                    if ($g) {
                        $g->update([
                            'nome'        => $grupo['nome'],
                            'min_selecao' => $grupo['min_selecao'] ?? 0,
                            'max_selecao' => $grupo['max_selecao'] ?? 1,
                            'obrigatorio' => isset($grupo['obrigatorio']),
                        ]);
                    }
                } else {
                    $g = $produto->gruposAdicionais()->create([
                        'nome'        => $grupo['nome'],
                        'min_selecao' => $grupo['min_selecao'] ?? 0,
                        'max_selecao' => $grupo['max_selecao'] ?? 1,
                        'obrigatorio' => isset($grupo['obrigatorio']),
                        'ordem'       => $grupo['ordem'] ?? 0,
                    ]);
                }
                $idsEnviados[] = $g->id;
                foreach ($grupo['adicionais'] ?? [] as $adc) {
                    if (!empty($adc['id'])) {
                        $g->adicionais()->find($adc['id'])?->update(['nome' => $adc['nome'], 'preco' => $adc['preco'] ?? 0]);
                    } else {
                        $g->adicionais()->create(['nome' => $adc['nome'], 'preco' => $adc['preco'] ?? 0, 'ordem' => $adc['ordem'] ?? 0]);
                    }
                }
            }
            $produto->gruposAdicionais()->whereNotIn('id', $idsEnviados)->delete();
        }

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true, 'produto' => $produto->fresh()->load('gruposAdicionais.adicionais')]);
        }

        return redirect()->route('admin.produtos.index')->with('sucesso', 'Produto atualizado com sucesso!');
    }

    public function destroy(Produto $produto)
    {
        $this->uploadService->deletar($produto->imagem_principal);
        $produto->delete();

        if (request()->expectsJson()) {
            return response()->json(['sucesso' => true]);
        }

        return redirect()->route('admin.produtos.index')->with('sucesso', 'Produto removido.');
    }

    public function uploadImagem(Request $request)
    {
        $request->validate(['arquivo' => 'required|image|max:20480']);
        $caminho = $this->uploadService->salvarImagemProduto($request->file('arquivo'));
        return response()->json(['sucesso' => true, 'caminho' => $caminho, 'url' => asset('storage/' . $caminho)]);
    }

    public function toggleStatus(Produto $produto)
    {
        $produto->update(['ativo' => !$produto->ativo]);
        return response()->json(['sucesso' => true, 'ativo' => $produto->ativo]);
    }

    public function reordenar(Request $request)
    {
        foreach ($request->ordem as $item) {
            Produto::where('id', $item['id'])->update(['ordem' => $item['ordem']]);
        }
        return response()->json(['sucesso' => true]);
    }

    private function validar(Request $request, ?int $ignorarId = null): array
    {
        return $request->validate([
            'nome'                => 'required|string|max:150',
            'categoria_id'        => 'nullable|exists:categorias,id',
            'descricao'           => 'nullable|string',
            'ingredientes'        => 'nullable|string',
            'preco'               => 'required|numeric|min:0',
            'preco_promocional'   => 'nullable|numeric|min:0',
            'peso_gramas'         => 'nullable|numeric|min:0',
            'estoque'             => 'nullable|integer|min:0',
            'controla_estoque'    => 'boolean',
            'ativo'               => 'boolean',
            'disponivel'          => 'boolean',
            'destaque'            => 'boolean',
            'novo'                => 'boolean',
            'tempo_preparo_min'   => 'integer|min:1',
            'ordem'               => 'integer|min:0',
        ], [
            'nome.required'  => 'O nome do produto é obrigatório.',
            'preco.required' => 'O preço é obrigatório.',
        ]);
    }
}
