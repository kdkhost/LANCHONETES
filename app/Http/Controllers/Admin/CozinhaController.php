<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CozinhaController extends Controller
{
    public function index(Request $request)
    {
        $loja = Auth::user()->loja;

        if (!$loja || !$loja->cozinha_ativo) {
            abort(403, 'Tela de cozinha não habilitada para esta loja.');
        }

        if ($loja->cozinha_pin) {
            $pinSessao = session('cozinha_pin_ok_' . $loja->id);
            if (!$pinSessao) {
                return view('admin.cozinha.pin', compact('loja'));
            }
        }

        $pedidos = Pedido::where('loja_id', $loja->id)
            ->whereIn('status', ['confirmado', 'em_preparo'])
            ->with(['itens.adicionais', 'usuario'])
            ->orderBy('created_at')
            ->get();

        return view('admin.cozinha.index', compact('pedidos', 'loja'));
    }

    public function verificarPin(Request $request)
    {
        $loja = Auth::user()->loja;
        $request->validate(['pin' => 'required|string']);

        if ($request->pin === $loja->cozinha_pin) {
            session(['cozinha_pin_ok_' . $loja->id => true]);
            return redirect()->route('admin.cozinha');
        }

        return back()->withErrors(['pin' => 'PIN incorreto.']);
    }

    public function pedidosAtivos()
    {
        $loja = Auth::user()->loja;

        $pedidos = Pedido::where('loja_id', $loja->id)
            ->whereIn('status', ['confirmado', 'em_preparo'])
            ->with(['itens.adicionais', 'usuario'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($pedido) {
                return [
                    'id'             => $pedido->id,
                    'numero'         => $pedido->numero,
                    'status'         => $pedido->status,
                    'tipo_entrega'   => $pedido->tipo_entrega,
                    'usuario_nome'   => $pedido->usuario->nome,
                    'observacoes'    => $pedido->observacoes,
                    'criado_em'      => $pedido->created_at->format('H:i'),
                    'minutos_atras'  => $pedido->created_at->diffInMinutes(now()),
                    'itens'          => $pedido->itens->map(fn($i) => [
                        'produto_nome'  => $i->produto_nome,
                        'quantidade'    => $i->quantidade,
                        'observacoes'   => $i->observacoes,
                        'adicionais'    => $i->adicionais->map(fn($a) => [
                            'nome'      => $a->adicional_nome,
                            'quantidade'=> $a->quantidade,
                        ]),
                    ]),
                ];
            });

        $ultimoId = Pedido::where('loja_id', $loja->id)
            ->whereIn('status', ['confirmado', 'em_preparo'])
            ->max('id');

        return response()->json(['pedidos' => $pedidos, 'ultimo_id' => $ultimoId]);
    }

    public function avancarStatus(Request $request, Pedido $pedido)
    {
        $loja = Auth::user()->loja;
        if ($pedido->loja_id !== $loja->id) abort(403);

        $proximo = match($pedido->status) {
            'confirmado' => 'em_preparo',
            'em_preparo' => 'pronto',
            default      => null,
        };

        if (!$proximo) {
            return response()->json(['erro' => 'Status não pode ser avançado.'], 422);
        }

        app(\App\Services\PedidoService::class)->atualizarStatus($pedido, $proximo);

        return response()->json(['sucesso' => true, 'novo_status' => $proximo]);
    }
}
