<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Avaliacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoClienteController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::where('usuario_id', Auth::id())
            ->with(['loja', 'itens', 'pagamento'])
            ->latest()
            ->paginate(15);

        return view('cliente.pedidos.index', compact('pedidos'));
    }

    public function show(Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);
        $pedido->load(['loja', 'itens.adicionais', 'pagamento', 'entrega']);
        return view('cliente.pedidos.show', compact('pedido'));
    }

    public function cancelar(Request $request, Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);
        if (!$pedido->podeCancelar()) {
            return response()->json(['erro' => 'Este pedido não pode mais ser cancelado.'], 422);
        }
        $pedido->atualizarStatus('cancelado', $request->motivo ?? 'Cancelado pelo cliente');
        return response()->json(['sucesso' => true]);
    }

    public function avaliar(Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);
        if ($pedido->status !== 'entregue') abort(422, 'Somente pedidos entregues podem ser avaliados.');
        if ($pedido->avaliacao) return redirect()->route('cliente.pedidos.show', $pedido)->with('info', 'Pedido já avaliado.');
        return view('cliente.pedidos.avaliar', compact('pedido'));
    }

    public function salvarAvaliacao(Request $request, Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);
        $request->validate([
            'nota'          => 'required|integer|min:1|max:5',
            'entrega_nota'  => 'nullable|integer|min:1|max:5',
            'comida_nota'   => 'nullable|integer|min:1|max:5',
            'embalagem_nota'=> 'nullable|integer|min:1|max:5',
            'comentario'    => 'nullable|string|max:500',
        ]);

        Avaliacao::create([
            'pedido_id'    => $pedido->id,
            'usuario_id'   => Auth::id(),
            'loja_id'      => $pedido->loja_id,
            'nota_loja'    => $request->nota,
            'nota_entrega' => $request->entrega_nota,
            'nota_comida'  => $request->comida_nota,
            'comentario'   => $request->comentario,
            'tags'         => $request->tags,
        ]);

        $media = Avaliacao::where('loja_id', $pedido->loja_id)->avg('nota_loja');
        $pedido->loja->update(['avaliacao_media' => $media]);

        return response()->json(['sucesso' => true, 'mensagem' => 'Avaliação enviada. Obrigado!']);
    }

    public function rastrear(Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);
        $pedido->load(['entrega.funcionario.usuario', 'loja', 'itens']);
        return view('cliente.pedidos.rastrear', compact('pedido'));
    }

    public function localizacao(Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) abort(403);
        $entrega = $pedido->entrega;

        if (!$entrega) {
            return response()->json(['erro' => 'Sem entrega ativa'], 404);
        }

        return response()->json([
            'latitude'      => $entrega->latitude_atual,
            'longitude'     => $entrega->longitude_atual,
            'status'        => $entrega->status,
            'atualizado_em' => $entrega->updated_at,
        ]);
    }
}
