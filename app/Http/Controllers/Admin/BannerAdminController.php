<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerAdminController extends Controller
{
    public function __construct(private UploadService $uploadService) {}

    public function index()
    {
        $lojaId  = Auth::user()->loja_id;
        $banners = Banner::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))->orderBy('ordem')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'imagem'      => 'nullable|image|max:20480',
            'imagem_path' => 'nullable|string',
            'titulo'      => 'nullable|string|max:150',
            'url'         => 'nullable|url|max:500',
            'ordem'       => 'integer|min:0',
            'valido_de'   => 'nullable|date',
            'valido_ate'  => 'nullable|date',
        ]);

        if (!$request->hasFile('imagem') && !$request->filled('imagem_path')) {
            return response()->json(['erro' => 'Envie uma imagem para o banner.'], 422);
        }

        if ($request->hasFile('imagem')) {
            $caminho = $this->uploadService->salvarBanner($request->file('imagem'));
        } else {
            $caminho = $request->imagem_path;
        }

        $banner = Banner::create([
            'loja_id'    => Auth::user()->loja_id,
            'imagem'     => $caminho,
            'titulo'     => $request->titulo,
            'url'        => $request->url,
            'ordem'      => $request->ordem ?? 0,
            'valido_de'  => $request->valido_de,
            'valido_ate' => $request->valido_ate,
            'ativo'      => true,
        ]);

        return response()->json(['sucesso' => true, 'banner' => $banner, 'imagem_url' => $banner->imagem_url]);
    }

    public function update(Request $request, Banner $banner)
    {
        $dados = $request->only(['titulo', 'url', 'ordem', 'ativo', 'valido_de', 'valido_ate']);
        if ($request->hasFile('imagem')) {
            $this->uploadService->deletar($banner->imagem);
            $dados['imagem'] = $this->uploadService->salvarBanner($request->file('imagem'));
        } elseif ($request->filled('imagem_path')) {
            $dados['imagem'] = $request->imagem_path;
        }
        $banner->update($dados);
        return response()->json(['sucesso' => true]);
    }

    public function destroy(Banner $banner)
    {
        $this->uploadService->deletar($banner->imagem);
        $banner->delete();
        return response()->json(['sucesso' => true]);
    }

    public function upload(Request $request)
    {
        $request->validate(['arquivo' => 'required|image|max:20480']);
        $caminho = $this->uploadService->salvarBanner($request->file('arquivo'));
        return response()->json(['sucesso' => true, 'caminho' => $caminho, 'url' => asset('storage/' . $caminho)]);
    }
}
