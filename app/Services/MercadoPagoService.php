<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Pagamento;
use App\Models\Loja;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Preference;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoPagoService
{
    private string $accessToken;
    private string $publicKey;
    private bool $sandbox;

    public function __construct(?Loja $loja = null)
    {
        if ($loja && $loja->mercadopago_access_token) {
            $this->accessToken = $loja->mercadopago_access_token;
            $this->publicKey   = $loja->mercadopago_public_key ?? '';
        } else {
            $this->accessToken = config('services.mercadopago.access_token', env('MERCADOPAGO_ACCESS_TOKEN'));
            $this->publicKey   = config('services.mercadopago.public_key', env('MERCADOPAGO_PUBLIC_KEY'));
        }
        $this->sandbox = (bool) env('MERCADOPAGO_SANDBOX', true);
        MercadoPagoConfig::setAccessToken($this->accessToken);
        MercadoPagoConfig::setRuntimeEnviroment($this->sandbox
            ? MercadoPagoConfig::LOCAL
            : MercadoPagoConfig::SERVER);
    }

    public function criarPreferencia(Pedido $pedido): array
    {
        try {
            $client = new PreferenceClient();

            $itens = [];
            foreach ($pedido->itens as $item) {
                $itens[] = [
                    'id'          => (string) $item->produto_id,
                    'title'       => $item->produto_nome,
                    'quantity'    => $item->quantidade,
                    'unit_price'  => (float) $item->produto_preco,
                    'currency_id' => 'BRL',
                ];
            }

            if ($pedido->taxa_entrega > 0) {
                $itens[] = [
                    'id'          => 'taxa_entrega',
                    'title'       => 'Taxa de Entrega',
                    'quantity'    => 1,
                    'unit_price'  => (float) $pedido->taxa_entrega,
                    'currency_id' => 'BRL',
                ];
            }

            $preferencia = $client->create([
                'items'              => $itens,
                'payer'              => [
                    'name'  => $pedido->usuario->nome,
                    'email' => $pedido->usuario->email,
                    'phone' => ['number' => $pedido->usuario->telefone ?? ''],
                ],
                'back_urls'          => [
                    'success' => route('cliente.pedido.sucesso', $pedido->id),
                    'failure' => route('cliente.pedido.falha', $pedido->id),
                    'pending' => route('cliente.pedido.pendente', $pedido->id),
                ],
                'auto_return'        => 'approved',
                'external_reference' => (string) $pedido->numero,
                'notification_url'   => route('webhook.mercadopago'),
                'expires'            => false,
                'statement_descriptor' => config('app.name'),
                'payment_methods'    => [
                    'excluded_payment_types' => [],
                    'installments'           => 12,
                ],
            ]);

            return [
                'sucesso'       => true,
                'preference_id' => $preferencia->id,
                'init_point'    => $preferencia->init_point,
                'sandbox_url'   => $preferencia->sandbox_init_point,
            ];
        } catch (Exception $e) {
            Log::error('MercadoPago criarPreferencia: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function criarPagamentoPix(Pedido $pedido): array
    {
        try {
            $client = new PaymentClient();

            $pagamento = $client->create([
                'transaction_amount' => (float) $pedido->total,
                'description'        => "Pedido {$pedido->numero} - " . config('app.name'),
                'payment_method_id'  => 'pix',
                'payer'              => [
                    'email'          => $pedido->usuario->email,
                    'first_name'     => explode(' ', $pedido->usuario->nome)[0],
                    'last_name'      => explode(' ', $pedido->usuario->nome, 2)[1] ?? '',
                    'identification' => [
                        'type'   => 'CPF',
                        'number' => preg_replace('/\D/', '', $pedido->usuario->cpf ?? ''),
                    ],
                ],
                'external_reference' => (string) $pedido->numero,
                'notification_url'   => route('webhook.mercadopago'),
                'date_of_expiration' => now()->addHours(24)->toIso8601String(),
            ]);

            if ($pagamento->status === 'pending') {
                return [
                    'sucesso'         => true,
                    'payment_id'      => $pagamento->id,
                    'status'          => $pagamento->status,
                    'qr_code'         => $pagamento->point_of_interaction->transaction_data->qr_code,
                    'qr_code_base64'  => $pagamento->point_of_interaction->transaction_data->qr_code_base64,
                    'expiracao'       => now()->addHours(24)->toISOString(),
                ];
            }

            return ['sucesso' => false, 'erro' => 'Erro ao gerar PIX: ' . $pagamento->status_detail];
        } catch (Exception $e) {
            Log::error('MercadoPago criarPagamentoPix: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function criarPagamentoCartao(Pedido $pedido, array $dadosCartao): array
    {
        try {
            $client = new PaymentClient();

            $dados = [
                'transaction_amount' => (float) $pedido->total,
                'token'              => $dadosCartao['token'],
                'description'        => "Pedido {$pedido->numero} - " . config('app.name'),
                'installments'       => (int) ($dadosCartao['parcelas'] ?? 1),
                'payment_method_id'  => $dadosCartao['metodo'],
                'payer'              => [
                    'email'          => $pedido->usuario->email,
                    'identification' => [
                        'type'   => 'CPF',
                        'number' => preg_replace('/\D/', '', $dadosCartao['cpf'] ?? $pedido->usuario->cpf ?? ''),
                    ],
                ],
                'external_reference' => (string) $pedido->numero,
                'notification_url'   => route('webhook.mercadopago'),
            ];

            $pagamento = $client->create($dados);

            return [
                'sucesso'        => true,
                'payment_id'     => $pagamento->id,
                'status'         => $pagamento->status,
                'status_detail'  => $pagamento->status_detail,
                'aprovado'       => $pagamento->status === 'approved',
            ];
        } catch (Exception $e) {
            Log::error('MercadoPago criarPagamentoCartao: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function consultarPagamento(string $paymentId): array
    {
        try {
            $client  = new PaymentClient();
            $payment = $client->get($paymentId);

            return [
                'sucesso'        => true,
                'id'             => $payment->id,
                'status'         => $payment->status,
                'status_detail'  => $payment->status_detail,
                'aprovado'       => $payment->status === 'approved',
                'valor'          => $payment->transaction_amount,
            ];
        } catch (Exception $e) {
            Log::error('MercadoPago consultarPagamento: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function processarWebhook(array $dados): bool
    {
        try {
            if (($dados['type'] ?? '') !== 'payment') return false;

            $paymentId = $dados['data']['id'] ?? null;
            if (!$paymentId) return false;

            $resultado = $this->consultarPagamento($paymentId);
            if (!$resultado['sucesso']) return false;

            $pagamento = Pagamento::where('mp_payment_id', $paymentId)->first();
            if (!$pagamento) {
                $pagamento = Pagamento::whereHas('pedido', function ($q) use ($resultado) {
                    $q->where('numero', $resultado['external_reference'] ?? '');
                })->first();
            }

            if (!$pagamento) return false;

            $status = match ($resultado['status']) {
                'approved' => 'aprovado',
                'rejected' => 'recusado',
                'cancelled'=> 'cancelado',
                'refunded' => 'reembolsado',
                'in_process', 'pending' => 'em_analise',
                default    => 'pendente',
            };

            $pagamento->update([
                'status'          => $status,
                'mp_status'       => $resultado['status'],
                'mp_status_detail'=> $resultado['status_detail'],
                'pago_em'         => $status === 'aprovado' ? now() : null,
            ]);

            if ($status === 'aprovado') {
                $pagamento->pedido->atualizarStatus('pagamento_aprovado');
            } elseif ($status === 'recusado') {
                $pagamento->pedido->atualizarStatus('cancelado', 'Pagamento recusado pelo MercadoPago');
            }

            return true;
        } catch (Exception $e) {
            Log::error('MercadoPago processarWebhook: ' . $e->getMessage());
            return false;
        }
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    // === MÉTODOS PARA PAGAMENTO DE PLANOS ===

    /**
     * Criar preferência de pagamento para plano
     */
    public function criarPreferenciaPlano(array $dados): array
    {
        try {
            $client = new PreferenceClient();
            $preference = $client->create([
                "items" => [
                    [
                        "id" => $dados['plano_id'],
                        "title" => $dados['plano_nome'],
                        "description" => $dados['descricao'],
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => $dados['valor'],
                    ]
                ],
                "payer" => [
                    "email" => $dados['email'],
                    "name" => $dados['nome'],
                    "identification" => [
                        "type" => "CPF",
                        "number" => $dados['cpf'] ?? null
                    ]
                ],
                "back_urls" => [
                    "success" => $dados['url_sucesso'],
                    "failure" => $dados['url_falha'],
                    "pending" => $dados['url_pendente']
                ],
                "auto_return" => "approved",
                "external_reference" => $dados['referencia_externa'],
                "notification_url" => $dados['webhook_url'],
                "statement_descriptor" => config('app.name'),
                "expires" => false,
                "binary_mode" => true
            ]);

            return [
                'success' => true,
                'preference_id' => $preference->id,
                'init_point' => $this->sandbox ? $preference->sandbox_init_point : $preference->init_point,
                'public_key' => $this->publicKey
            ];

        } catch (Exception $e) {
            Log::error('MercadoPago criarPreferenciaPlano: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar informações de pagamento do MercadoPago
     */
    public function buscarPagamento(string $paymentId): ?array
    {
        try {
            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            return [
                'id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'external_reference' => $payment->external_reference,
                'amount' => $payment->transaction_amount,
                'currency_id' => $payment->currency_id,
                'payment_method_id' => $payment->payment_method_id,
                'payment_type_id' => $payment->payment_type_id,
                'payer_email' => $payment->payer->email,
                'date_created' => $payment->date_created,
                'date_approved' => $payment->date_approved,
                'metadata' => $payment->metadata ?? []
            ];

        } catch (Exception $e) {
            Log::error('MercadoPago buscarPagamento: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Processar webhook de pagamento de plano
     */
    public function processarWebhookPlano(array $data): bool
    {
        try {
            $paymentId = $data['data']['id'] ?? null;
            if (!$paymentId) {
                return false;
            }

            $payment = $this->buscarPagamento($paymentId);
            if (!$payment) {
                return false;
            }

            // Apenas processar pagamentos aprovados
            if ($payment['status'] !== 'approved') {
                return false;
            }

            // Extrair informações da referência externa
            $referencia = $payment['external_reference'];
            if (!str_starts_with($referencia, 'plano_')) {
                return false; // Não é pagamento de plano
            }

            $parts = explode('_', $referencia);
            $assinaturaId = $parts[1] ?? null;
            $lojaId = $parts[2] ?? null;

            if (!$assinaturaId || !$lojaId) {
                return false;
            }

            // Atualizar assinatura
            $assinatura = \App\Models\Assinatura::find($assinaturaId);
            if (!$assinatura || $assinatura->loja_id != $lojaId) {
                return false;
            }

            if ($assinatura->status === 'ativa') {
                return true; // Já está ativa
            }

            // Atualizar status e dados do pagamento
            $assinatura->update([
                'status' => 'ativa',
                'metodo_pagamento' => 'mercadopago',
                'gateway_id' => $payment['id'],
                'valor_pago' => $payment['amount'], // Valor já está em formato decimal
                'notas' => "Pago via MercadoPago em {$payment['date_approved']}",
            ]);

            // Atualizar limitações da loja
            app(PlanoService::class)->atualizarLimitacoesLoja($assinatura->loja);

            // Enviar email de confirmação
            try {
                \Mail::to($assinatura->loja->email)->send(new \App\Mail\AssinaturaAtivada($assinatura));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar email de ativação de assinatura: ' . $e->getMessage());
            }

            Log::info('Pagamento de plano aprovado via webhook', [
                'assinatura_id' => $assinatura->id,
                'loja_id' => $assinatura->loja_id,
                'payment_id' => $paymentId,
                'valor' => $payment['amount']
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('MercadoPago processarWebhookPlano: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gerar referência externa para pagamento de plano
     */
    public static function gerarReferenciaExterna(int $assinaturaId, int $lojaId): string
    {
        return "plano_{$assinaturaId}_{$lojaId}_" . time();
    }
}
