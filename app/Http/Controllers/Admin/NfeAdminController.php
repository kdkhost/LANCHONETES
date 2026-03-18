<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotaFiscal;
use App\Models\Pedido;
use App\Services\NfeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NfeAdminController extends Controller
{
    public function index(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403, 'Nenhuma loja associada ao seu usuário.');

        $notas = NotaFiscal::where('loja_id', $loja->id)
            ->with('pedido.usuario')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->de,  fn($q) => $q->whereDate('created_at', '>=', $request->de))
            ->when($request->ate, fn($q) => $q->whereDate('created_at', '<=', $request->ate))
            ->latest()
            ->paginate(20);

        return view('admin.nfe.index', compact('notas', 'loja'));
    }

    public function emitir(Request $request, Pedido $pedido)
    {
        $loja = Auth::user()->loja;
        if ($pedido->loja_id !== $loja->id) abort(403);

        if (!$loja->nfe_ativo) {
            return response()->json(['erro' => 'NFe não habilitada para esta loja.'], 422);
        }

        if ($pedido->notaFiscal?->estaAutorizada()) {
            return response()->json(['erro' => 'Nota fiscal já emitida para este pedido.'], 422);
        }

        try {
            $nfeService = new NfeService($loja);
            $nota = $nfeService->emitir($pedido);

            return response()->json([
                'sucesso'     => true,
                'status'      => $nota->status,
                'status_label'=> $nota->status_label,
                'chave'       => $nota->chave_acesso,
                'url_danfe'   => $nota->url_danfe,
                'mensagem'    => $nota->estaAutorizada()
                    ? 'Nota fiscal emitida com sucesso!'
                    : 'Nota fiscal rejeitada: ' . $nota->motivo_rejeicao,
            ]);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function cancelar(Request $request, NotaFiscal $nota)
    {
        $loja = Auth::user()->loja;
        if ($nota->loja_id !== $loja->id) abort(403);

        $request->validate(['justificativa' => 'required|string|min:15|max:255']);

        try {
            $nfeService = new NfeService($loja);
            $ok = $nfeService->cancelar($nota, $request->justificativa);

            if ($ok) {
                return response()->json(['sucesso' => true, 'mensagem' => 'Nota fiscal cancelada.']);
            }
            return response()->json(['erro' => 'Não foi possível cancelar a nota fiscal.'], 422);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function danfe(NotaFiscal $nota)
    {
        $loja = Auth::user()->loja;
        if ($nota->loja_id !== $loja->id) abort(403);

        if (!$nota->url_danfe) abort(404, 'DANFE não disponível.');

        return redirect($nota->url_danfe);
    }
}
