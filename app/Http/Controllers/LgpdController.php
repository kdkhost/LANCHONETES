<?php

namespace App\Http\Controllers;

use App\Models\LgpdAceite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LgpdController extends Controller
{
    public function termos(Request $request)
    {
        $loja      = $this->resolverLoja($request);
        $lojaAtual = $loja;
        return view('lgpd.termos', compact('loja', 'lojaAtual'));
    }

    public function politica(Request $request)
    {
        $loja      = $this->resolverLoja($request);
        $lojaAtual = $loja;
        return view('lgpd.politica', compact('loja', 'lojaAtual'));
    }

    private function resolverLoja(Request $request): ?\App\Models\Loja
    {
        if (app()->bound('loja_atual')) {
            return app('loja_atual');
        }
        $slug = $request->segment(1);
        if ($slug && !in_array($slug, ['termos-de-uso', 'politica-privacidade', 'lgpd'])) {
            return \App\Models\Loja::where('slug', $slug)->first();
        }
        return null;
    }

    public function aceitar(Request $request)
    {
        $request->validate([
            'tipo'    => 'required|in:cookies,termos,ambos',
            'loja_id' => 'required|integer|exists:lojas,id',
        ]);

        LgpdAceite::create([
            'usuario_id' => Auth::id(),
            'loja_id'    => $request->loja_id,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'versao'     => '1.0',
            'tipo'       => $request->tipo,
        ]);

        return response()->json(['sucesso' => true]);
    }
}
