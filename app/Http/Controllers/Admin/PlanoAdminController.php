<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plano;
use App\Models\Assinatura;
use App\Services\PlanoService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanoAdminController extends Controller
{
    public function __construct(private PlanoService $planoService)
    {
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
    }

    public function index(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $assinatura = $loja->assinatura;
        $planos = Plano::ativos()->ordenados()->get();
        
        return view('admin.planos.index', compact('loja', 'assinatura', 'planos'));
    }

    public function upgrade(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $assinatura = $loja->assinatura;
        $planos = Plano::ativos()->ordenados()->get();
        
        return view('admin.planos.upgrade', compact('loja', 'assinatura', 'planos'));
    }

    public function assinaturas(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $assinaturas = Assinatura::where('loja_id', $loja->id)
            ->with('plano')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.planos.assinaturas', compact('loja', 'assinaturas'));
    }

    public function checkout(Request $request, Plano $plano)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        if ($plano->slug === 'gratuita') {
            return redirect()->route('admin.planos.index')
                ->with('erro', 'Selecione um plano pago para continuar.');
        }

        $periodo = $request->get('periodo', 'mensal');
        $valor = $periodo === 'anual' ? $plano->preco_anual : $plano->preco_mensal;

        return view('admin.planos.checkout', compact('loja', 'plano', 'periodo', 'valor'));
    }

    public function processarPagamento(Request $request, Plano $plano)
    {
        $request->validate([
            'periodo' => 'required|in:mensal,anual',
            'metodo_pagamento' => 'required|in:mercadopago,manual',
        ]);

        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $periodo = $request->periodo;
        $valor = $periodo === 'anual' ? $plano->preco_anual : $plano->preco_mensal;

        if ($request->metodo_pagamento === 'manual') {
            // Pagamento manual (admin pode aprovar depois)
            $this->planoService->criarAssinatura($loja, $plano, [
                'periodo' => $periodo,
                'valor_pago' => $valor,
                'metodo_pagamento' => 'manual',
                'notas' => 'Aguardando confirmação de pagamento',
            ]);

            return redirect()->route('admin.planos.assinaturas')
                ->with('sucesso', 'Assinatura criada! Aguarde a confirmação do pagamento.');
        }

        // Integração com MercadoPago (simulada por enquanto)
        return redirect()->route('admin.planos.checkout.mercadopago', [
            'plano' => $plano->id,
            'periodo' => $periodo,
        ]);
    }

    public function mercadoPagoCheckout(Request $request, Plano $plano)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $periodo = $request->get('periodo', 'mensal');
        $valor = $periodo === 'anual' ? $plano->preco_anual : $plano->preco_mensal;

        // Criar assinatura pendente primeiro
        $assinatura = $this->planoService->criarAssinatura($loja, $plano, [
            'periodo' => $periodo,
            'valor_pago' => $valor,
            'metodo_pagamento' => 'mercadopago',
            'notas' => 'Aguardando pagamento via MercadoPago',
        ]);

        // Configurar MercadoPago
        $mpService = new MercadoPagoService($loja);

        // Criar preferência de pagamento
        $resultado = $mpService->criarPreferenciaPlano([
            'plano_id' => $plano->id,
            'plano_nome' => "Plano {$plano->nome} - " . ucfirst($periodo),
            'descricao' => "Assinatura {$periodo} do plano {$plano->nome}",
            'valor' => $valor, // MercadoPago trabalha com valor decimal
            'email' => $loja->email,
            'nome' => $loja->nome,
            'cpf' => null, // Opcional, pode ser adicionado no futuro
            'url_sucesso' => route('admin.planos.mercadopago.sucesso'),
            'url_falha' => route('admin.planos.mercadopago.falha'),
            'url_pendente' => route('admin.planos.mercadopago.pendente'),
            'webhook_url' => route('webhook.mercadopago.plano'),
            'referencia_externa' => MercadoPagoService::gerarReferenciaExterna($assinatura->id, $loja->id),
        ]);

        if (!$resultado['success']) {
            return redirect()->back()
                ->with('erro', 'Erro ao criar pagamento: ' . $resultado['error']);
        }

        // Redirecionar para o checkout do MercadoPago
        return redirect()->away($resultado['init_point']);
    }

    public function mercadoPagoSucesso(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $paymentId = $request->get('payment_id');
        if (!$paymentId) {
            return redirect()->route('admin.planos.index')
                ->with('erro', 'ID do pagamento não encontrado.');
        }

        // Verificar status do pagamento
        $mpService = new MercadoPagoService($loja);
        $payment = $mpService->buscarPagamento($paymentId);

        if (!$payment) {
            return redirect()->route('admin.planos.index')
                ->with('erro', 'Não foi possível verificar o pagamento.');
        }

        if ($payment['status'] !== 'approved') {
            return redirect()->route('admin.planos.index')
                ->with('alerta', 'Seu pagamento está sendo processado. Status: ' . $payment['status']);
        }

        return redirect()->route('admin.planos.index')
            ->with('sucesso', 'Pagamento aprovado! Sua assinatura está ativa.');
    }

    public function mercadoPagoFalha(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $paymentId = $request->get('payment_id');
        
        return redirect()->route('admin.planos.index')
            ->with('erro', 'Seu pagamento não foi aprovado. Por favor, tente novamente ou entre em contato com o suporte.');
    }

    public function mercadoPagoPendente(Request $request)
    {
        $loja = Auth::user()->loja;
        if (!$loja) abort(403);

        $paymentId = $request->get('payment_id');
        
        return redirect()->route('admin.planos.index')
            ->with('alerta', 'Seu pagamento está sendo processado. Assim que for aprovado, sua assinatura será ativada automaticamente.');
    }

    public function detalhesPagamento(Request $request, string $paymentId)
    {
        $loja = Auth::user()->loja;
        if (!$loja) {
            return response()->json(['success' => false, 'error' => 'Loja não encontrada']);
        }

        $mpService = new MercadoPagoService($loja);
        $payment = $mpService->buscarPagamento($paymentId);

        if (!$payment) {
            return response()->json(['success' => false, 'error' => 'Pagamento não encontrado']);
        }

        return response()->json(['success' => true, 'payment' => $payment]);
    }

    // Métodos para Super Admin gerenciar planos
    public function planos(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $planos = Plano::ordenados()->paginate(20);
        return view('admin.planos.gerenciar', compact('planos'));
    }

    public function criarPlano(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $request->validate([
            'nome' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:planos',
            'descricao' => 'nullable|string',
            'preco_mensal' => 'required|numeric|min:0',
            'preco_anual' => 'nullable|numeric|min:0',
            'recursos' => 'required|array',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
        ]);

        $plano = Plano::create($request->all());

        return redirect()->route('admin.planos.gerenciar')
            ->with('sucesso', 'Plano criado com sucesso!');
    }

    public function editarPlano(Request $request, Plano $plano)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $request->validate([
            'nome' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:planos,slug,' . $plano->id,
            'descricao' => 'nullable|string',
            'preco_mensal' => 'required|numeric|min:0',
            'preco_anual' => 'nullable|numeric|min:0',
            'recursos' => 'required|array',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
        ]);

        $plano->update($request->all());

        return redirect()->route('admin.planos.gerenciar')
            ->with('sucesso', 'Plano atualizado com sucesso!');
    }

    public function excluirPlano(Plano $plano)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);
        
        if ($plano->assinaturas()->where('status', 'ativa')->exists()) {
            return redirect()->route('admin.planos.gerenciar')
                ->with('erro', 'Não é possível excluir um plano com assinaturas ativas.');
        }

        $plano->delete();

        return redirect()->route('admin.planos.gerenciar')
            ->with('sucesso', 'Plano excluído com sucesso!');
    }

    public function aprovarPagamento(Request $request, Assinatura $assinatura)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        if ($assinatura->status !== 'ativa') {
            $assinatura->update([
                'status' => 'ativa',
                'notas' => 'Pagamento aprovado manualmente em ' . now()->format('d/m/Y H:i'),
            ]);

            $this->planoService->atualizarLimitacoesLoja($assinatura->loja);
        }

        return redirect()->back()
            ->with('sucesso', 'Pagamento aprovado com sucesso!');
    }

    public function cancelarAssinatura(Request $request, Assinatura $assinatura)
    {
        $loja = Auth::user()->loja;
        if (!$loja || $assinatura->loja_id !== $loja->id) abort(403);

        $this->planoService->cancelarAssinatura($loja);

        return redirect()->route('admin.planos.index')
            ->with('sucesso', 'Assinatura cancelada com sucesso.');
    }
}
