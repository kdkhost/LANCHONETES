<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoriaAdminController extends Controller
{
    public function __construct(private UploadService $uploadService) {}

    public function index()
    {
        $lojaId     = Auth::user()->loja_id;
        $categorias = Categoria::with(['subcategorias', 'produtos' => fn($q) => $q->select('id', 'categoria_id')])
            ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->whereNull('categoria_pai_id')
            ->orderBy('ordem')
            ->get();
        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'             => 'required|string|max:100',
            'descricao'        => 'nullable|string',
            'icone'            => 'nullable|string|max:50',
            'ordem'            => 'integer|min:0',
            'ativo'            => 'boolean',
            'destaque'         => 'boolean',
            'categoria_pai_id' => 'nullable|exists:categorias,id',
        ]);

        $lojaId = Auth::user()->loja_id;
        $slug   = Str::slug($request->nome);
        $base   = $slug;
        $i      = 1;
        while (Categoria::where('loja_id', $lojaId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $categoria = Categoria::create(array_merge($request->except(['imagem', 'imagem_path']), [
            'loja_id' => $lojaId,
            'slug'    => $slug,
        ]));

        if ($request->hasFile('imagem')) {
            $caminho = $this->uploadService->salvarImagem($request->file('imagem'), 'categorias', 400, 400);
            $categoria->update(['imagem' => $caminho]);
        } elseif ($request->filled('imagem_path')) {
            $categoria->update(['imagem' => $request->imagem_path]);
        }

        return response()->json(['sucesso' => true, 'categoria' => $categoria]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nome'     => 'required|string|max:100',
            'descricao'=> 'nullable|string',
            'icone'    => 'nullable|string|max:50',
            'ordem'    => 'integer|min:0',
            'ativo'    => 'boolean',
            'destaque' => 'boolean',
        ]);

        $dados = $request->except(['imagem', 'imagem_path']);
        if ($request->hasFile('imagem')) {
            $this->uploadService->deletar($categoria->imagem);
            $dados['imagem'] = $this->uploadService->salvarImagem($request->file('imagem'), 'categorias', 400, 400);
        } elseif ($request->filled('imagem_path')) {
            $dados['imagem'] = $request->imagem_path;
        }

        $categoria->update($dados);
        return response()->json(['sucesso' => true, 'categoria' => $categoria->fresh()]);
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->produtos()->count() > 0) {
            return response()->json(['erro' => 'Categoria possui produtos vinculados.'], 422);
        }
        $this->uploadService->deletar($categoria->imagem);
        $categoria->delete();
        return response()->json(['sucesso' => true]);
    }

    public function uploadImagem(Request $request)
    {
        $request->validate(['arquivo' => 'required|image|max:5120']);
        $caminho = $this->uploadService->salvarImagem($request->file('arquivo'), 'categorias', 400, 400);
        return response()->json(['sucesso' => true, 'caminho' => $caminho, 'url' => asset('storage/' . $caminho)]);
    }

    public function reordenar(Request $request)
    {
        foreach ($request->ordem as $item) {
            Categoria::where('id', $item['id'])->update(['ordem' => $item['ordem']]);
        }
        return response()->json(['sucesso' => true]);
    }
}
