<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CupomAdminController extends Controller
{
    public function index()
    {
        $lojaId = Auth::user()->loja_id;
        $cupons = Cupom::when($lojaId, fn($q) => $q->where('loja_id', $lojaId))->latest()->paginate(20);
        return view('admin.cupons.index', compact('cupons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'              => 'required|string|max:50|unique:cupons,codigo',
            'tipo'                => 'required|in:percentual,fixo,frete_gratis',
            'valor'               => 'required_unless:tipo,frete_gratis|numeric|min:0',
            'valor_minimo_pedido' => 'nullable|numeric|min:0',
            'usos_maximos'        => 'nullable|integer|min:1',
            'valido_de'           => 'nullable|date',
            'valido_ate'          => 'nullable|date|after_or_equal:valido_de',
        ]);

        $cupom = Cupom::create(array_merge($request->all(), ['loja_id' => Auth::user()->loja_id, 'codigo' => strtoupper($request->codigo)]));
        return response()->json(['sucesso' => true, 'cupom' => $cupom]);
    }

    public function update(Request $request, Cupom $cupom)
    {
        $request->validate(['ativo' => 'boolean']);
        $cupom->update($request->all());
        return response()->json(['sucesso' => true]);
    }

    public function destroy(Cupom $cupom)
    {
        $cupom->delete();
        return response()->json(['sucesso' => true]);
    }
}
