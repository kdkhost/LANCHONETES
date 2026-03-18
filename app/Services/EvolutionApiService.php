<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Loja;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EvolutionApiService
{
    private string $baseUrl;
    private string $apiKey;
    private string $instance;

    public function __construct(?Loja $loja = null)
    {
        $this->baseUrl  = rtrim(env('EVOLUTION_API_URL', ''), '/');
        $this->apiKey   = env('EVOLUTION_API_KEY', '');
        $this->instance = $loja?->evolution_instance ?? env('EVOLUTION_INSTANCE', 'lanchonete');
    }

    private function enviar(string $numero, string $mensagem): bool
    {
        try {
            $telefone = $this->formatarTelefone($numero);
            if (!$telefone) return false;

            $resposta = Http::withHeaders([
                'apikey'        => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                'number'  => $telefone,
                'text'    => $mensagem,
                'delay'   => 500,
            ]);

            if ($resposta->successful()) return true;

            Log::warning('Evolution API erro ao enviar: ' . $resposta->body());
            return false;
        } catch (Exception $e) {
            Log::error('Evolution API enviar: ' . $e->getMessage());
            return false;
        }
    }

    private function enviarImagem(string $numero, string $urlImagem, string $legenda = ''): bool
    {
        try {
            $telefone = $this->formatarTelefone($numero);
            if (!$telefone) return false;

            $resposta = Http::withHeaders([
                'apikey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/message/sendMedia/{$this->instance}", [
                'number'   => $telefone,
                'mediatype'=> 'image',
                'media'    => $urlImagem,
                'caption'  => $legenda,
            ]);

            return $resposta->successful();
        } catch (Exception $e) {
            Log::error('Evolution API enviarImagem: ' . $e->getMessage());
            return false;
        }
    }

    private function formatarTelefone(string $numero): ?string
    {
        $limpo = preg_replace('/\D/', '', $numero);
        if (strlen($limpo) === 11) return '55' . $limpo . '@s.whatsapp.net';
        if (strlen($limpo) === 13 && str_starts_with($limpo, '55')) return $limpo . '@s.whatsapp.net';
        if (strlen($limpo) === 10) return '55' . $limpo . '@s.whatsapp.net';
        return null;
    }

    public function notificarPedidoNovo(Pedido $pedido): void
    {
        $loja    = $pedido->loja;
        $usuario = $pedido->usuario;

        // ── Notificação para a LOJA ──────────────────────────────────────────
        if ($loja->whatsapp && $loja->notificacoes_whatsapp) {
            $linhaItens = '';
            foreach ($pedido->itens as $item) {
                $linhaItens .= "  • {$item->quantidade}x {$item->produto_nome}";
                if ($item->observacoes) $linhaItens .= " _(obs: {$item->observacoes})_";
                $linhaItens .= "\n";
            }
            $tipoEntrega = $pedido->tipo_entrega === 'retirada' ? '� Retirada no local' : '🛵 Entrega no endereço';
            $metodoPag   = config('lanchonete.pagamento.metodos')[$pedido->pagamento?->metodo ?? ''] ?? 'N/A';
            $endereco    = $pedido->tipo_entrega !== 'retirada'
                ? "\n📍 Endereço: {$pedido->endereco_logradouro}, {$pedido->endereco_numero} — {$pedido->endereco_bairro}"
                : '';

            $msgLoja = $loja->wppTemplate('pedido_novo');
            if (!$msgLoja) {
                $msgLoja = "🔔 *NOVO PEDIDO!* 🔔\n"
                    . str_repeat('─', 25) . "\n"
                    . "📋 *Pedido:* {$pedido->numero}\n"
                    . "👤 *Cliente:* {$usuario->nome}\n"
                    . "📱 *Telefone:* " . ($usuario->whatsapp ?: $usuario->telefone ?: 'N/I') . "\n"
                    . str_repeat('─', 25) . "\n"
                    . "🛒 *Itens do Pedido:*\n"
                    . $linhaItens
                    . str_repeat('─', 25) . "\n"
                    . "💰 *Subtotal:* R$ " . number_format($pedido->subtotal, 2, ',', '.') . "\n";

                if ($pedido->desconto > 0) {
                    $msgLoja .= "🎟 *Desconto:* -R$ " . number_format($pedido->desconto, 2, ',', '.') . "\n";
                }

                $msgLoja .= "💵 *TOTAL: R$ " . number_format($pedido->total, 2, ',', '.') . "*\n"
                    . "💳 *Pagamento:* {$metodoPag}\n"
                    . "🚚 *Tipo:* {$tipoEntrega}"
                    . $endereco . "\n";

                if ($pedido->observacoes) {
                    $msgLoja .= "📝 *Obs:* {$pedido->observacoes}\n";
                }

                $msgLoja .= str_repeat('─', 25) . "\n"
                    . "⏱ Tempo estimado: *{$pedido->tempo_estimado_min} min*\n"
                    . "🌐 Painel: " . route('admin.pedidos.show', $pedido);
            }

            $this->enviar($loja->whatsapp, $msgLoja);
        }

        // ── Notificação para o CLIENTE ───────────────────────────────────────
        if ($usuario->whatsapp) {
            $linhaItens = '';
            foreach ($pedido->itens as $item) {
                $linhaItens .= "  • {$item->quantidade}x {$item->produto_nome}\n";
            }

            $msgCliente = $loja->wppTemplate('pedido_confirmado_cliente');
            if (!$msgCliente) {
                $msgCliente = "✅ *Pedido Recebido com Sucesso!*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$usuario->nome}*! 😊\n\n"
                    . "Seu pedido foi recebido pela *{$loja->nome}* e está aguardando confirmação!\n\n"
                    . "📋 *Pedido:* {$pedido->numero}\n"
                    . "🛒 *Itens:*\n" . $linhaItens
                    . "� *Total: R$ " . number_format($pedido->total, 2, ',', '.') . "*\n"
                    . "⏱ *Estimativa:* {$pedido->tempo_estimado_min} min\n\n"
                    . "Você receberá uma mensagem a cada atualização do seu pedido.\n"
                    . "📲 Acompanhe seus pedidos: " . route('cliente.pedidos.index');
            }

            $this->enviar($usuario->whatsapp, $msgCliente);
        }
    }

    public function notificarStatusPedido(Pedido $pedido, string $status): void
    {
        $usuario = $pedido->usuario;
        if (!$usuario->whatsapp) return;

        $loja   = $pedido->loja;
        $nome   = $usuario->nome;
        $numero = $pedido->numero;
        $total  = 'R$ ' . number_format($pedido->total, 2, ',', '.');
        $link   = '';

        if ($status === 'saiu_para_entrega' && $pedido->entrega) {
            $link = "\n🗺 *Rastreie em tempo real:*\n" . route('rastreamento.publico', $pedido->entrega->token_rastreamento);
        }

        $templateKey = 'status_' . $status;
        $mensagem    = $loja->wppTemplate($templateKey);

        if (!$mensagem) {
            $mensagem = match($status) {
                'pagamento_aprovado' =>
                    "💳✅ *Pagamento Aprovado!*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*!\n\n"
                    . "Seu pagamento do pedido *{$numero}* foi confirmado!\n"
                    . "💰 Valor pago: *{$total}*\n\n"
                    . "Aguarde a confirmação da *{$loja->nome}*. Em breve você receberá uma atualização!",

                'confirmado' =>
                    "✅ *Pedido Confirmado!*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*!\n\n"
                    . "A *{$loja->nome}* confirmou seu pedido *{$numero}*!\n"
                    . "⏱ Tempo estimado: *{$pedido->tempo_estimado_min} min*\n\n"
                    . "Vamos começar a preparar seu pedido! Fique de olho nas atualizações 😉",

                'em_preparo' =>
                    "👨‍🍳 *Na Cozinha Agora!*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*!\n\n"
                    . "Seu pedido *{$numero}* está sendo preparado com muito carinho pelos nossos cozinheiros! 😋\n\n"
                    . "⏳ Aguarde, está quase chegando!",

                'pronto' =>
                    ($pedido->tipo_entrega === 'retirada'
                        ? "🍽 *Pronto para Retirada!*\n"
                            . str_repeat('─', 25) . "\n"
                            . "Olá, *{$nome}*!\n\n"
                            . "Seu pedido *{$numero}* está PRONTO!\n"
                            . "🏪 Venha retirar na *{$loja->nome}*\n"
                            . "📍 {$loja->logradouro}, {$loja->numero} — {$loja->bairro}"
                        : "🍽 *Pedido Pronto!*\n"
                            . str_repeat('─', 25) . "\n"
                            . "Olá, *{$nome}*!\n\n"
                            . "Seu pedido *{$numero}* está PRONTO e logo sairá para entrega!\n"
                            . "🛵 Um entregador será designado em instantes!"
                    ),

                'saiu_para_entrega' =>
                    "🛵 *Saiu para Entrega!*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*!\n\n"
                    . "Seu pedido *{$numero}* saiu para entrega e está a caminho!\n"
                    . "🏍 Entregador: *" . ($pedido->entrega?->entregador?->usuario?->nome ?? 'A caminho') . "*\n"
                    . $link,

                'entregue' =>
                    "🎉 *Pedido Entregue!*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*!\n\n"
                    . "Seu pedido *{$numero}* foi entregue! Bom apetite! 😊\n\n"
                    . "⭐ Gostou? Deixe sua avaliação:\n"
                    . route('cliente.avaliar', $pedido->id) . "\n\n"
                    . "Obrigado por escolher a *{$loja->nome}*! Volte sempre! 🙏",

                'cancelado' =>
                    "❌ *Pedido Cancelado*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*,\n\n"
                    . "Infelizmente seu pedido *{$numero}* foi cancelado.\n"
                    . "📝 *Motivo:* " . ($pedido->motivo_cancelamento ?? 'Não informado') . "\n\n"
                    . "Se tiver dúvidas, entre em contato:\n"
                    . "📱 " . ($loja->whatsapp ?: $loja->telefone ?: '') . "\n\n"
                    . "Lamentamos o inconveniente. Esperamos vê-lo em breve! 🙏",

                'recusado' =>
                    "⛔ *Pedido Recusado*\n"
                    . str_repeat('─', 25) . "\n"
                    . "Olá, *{$nome}*,\n\n"
                    . "Infelizmente não foi possível atender seu pedido *{$numero}* no momento.\n\n"
                    . "Por favor, tente novamente mais tarde ou entre em contato conosco.\n"
                    . "📱 " . ($loja->whatsapp ?: $loja->telefone ?: ''),

                default => null,
            };
        }

        if (!$mensagem) return;
        $this->enviar($usuario->whatsapp, $mensagem);
    }

    public function notificarPagamentoPix(Pedido $pedido, string $qrCode, string $qrCodeBase64): void
    {
        $usuario = $pedido->usuario;
        if (!$usuario->whatsapp) return;

        $loja     = $pedido->loja;
        $total    = 'R$ ' . number_format($pedido->total, 2, ',', '.');
        $expira   = now()->addHours(24)->format('d/m/Y \à\s H:i');

        $mensagem = $loja->wppTemplate('pagamento_pix');
        if (!$mensagem) {
            $mensagem = "💰 *Pagamento via PIX*\n"
                . str_repeat('─', 25) . "\n"
                . "Olá, *{$usuario->nome}*!\n\n"
                . "📋 Pedido: *{$pedido->numero}*\n"
                . "💵 Valor: *{$total}*\n"
                . "⏰ Expira em: {$expira}\n\n"
                . "📋 *Chave PIX Copia e Cola:*\n"
                . "`{$qrCode}`\n\n"
                . "_Ou escaneie o QR Code enviado acima._\n\n"
                . "✅ Após o pagamento, seu pedido será confirmado automaticamente!";
        }

        $this->enviar($usuario->whatsapp, $mensagem);

        if ($qrCodeBase64) {
            $dataUrl = 'data:image/png;base64,' . $qrCodeBase64;
            $this->enviarImagem($usuario->whatsapp, $dataUrl, "QR Code PIX — Pedido {$pedido->numero}");
        }
    }

    public function enviarLinkRastreamento(Pedido $pedido): void
    {
        $usuario = $pedido->usuario;
        if (!$usuario->whatsapp || !$pedido->entrega) return;

        $loja      = $pedido->loja;
        $link      = route('rastreamento.publico', $pedido->entrega->token_rastreamento);
        $nomeEnt   = $pedido->entrega->entregador?->usuario?->nome ?? 'A caminho';

        $mensagem = $loja->wppTemplate('link_rastreamento');
        if (!$mensagem) {
            $mensagem = "� *Acompanhe Sua Entrega ao Vivo!*\n"
                . str_repeat('─', 25) . "\n"
                . "Olá, *{$usuario->nome}*!\n\n"
                . "📋 Pedido: *{$pedido->numero}*\n"
                . "🏍 Entregador: *{$nomeEnt}*\n\n"
                . "Clique no link abaixo para ver o entregador no mapa em tempo real:\n"
                . "👉 {$link}\n\n"
                . "_O link atualiza automaticamente a cada 15 segundos._";
        }

        $this->enviar($usuario->whatsapp, $mensagem);
    }

    public function notificarCancelamentoCliente(Pedido $pedido, string $motivo = ''): void
    {
        $usuario = $pedido->usuario;
        if (!$usuario->whatsapp) return;
        $this->notificarStatusPedido($pedido, 'cancelado');
    }

    public function notificarPedidoCozinha(Pedido $pedido): void
    {
        $loja = $pedido->loja;
        if (!$loja->whatsapp || !$loja->notificacoes_whatsapp) return;

        $linhaItens = '';
        foreach ($pedido->itens as $item) {
            $linhaItens .= "  🍴 {$item->quantidade}x {$item->produto_nome}";
            if ($item->observacoes) $linhaItens .= " ⚠️ OBS: {$item->observacoes}";
            $linhaItens .= "\n";
        }

        $mensagem = "�‍🍳 *COZINHA — NOVO PEDIDO ACEITO!*\n"
            . str_repeat('─', 25) . "\n"
            . "📋 *Pedido:* {$pedido->numero}\n"
            . "🚚 *Tipo:* " . ($pedido->tipo_entrega === 'retirada' ? 'Retirada' : 'Entrega') . "\n"
            . str_repeat('─', 25) . "\n"
            . $linhaItens
            . str_repeat('─', 25) . "\n";

        if ($pedido->observacoes) {
            $mensagem .= "📝 *Obs geral:* {$pedido->observacoes}\n";
        }

        $mensagem .= "⏱ Prazo: *{$pedido->tempo_estimado_min} min*";

        $this->enviar($loja->whatsapp, $mensagem);
    }

    public function enviarMensagemManual(string $numero, string $mensagem): bool
    {
        return $this->enviar($numero, $mensagem);
    }

    public function notificarSolicitacaoAvaliacao(Pedido $pedido): void
    {
        $usuario = $pedido->usuario;
        if (!$usuario->whatsapp) return;

        $loja     = $pedido->loja;
        $link     = route('cliente.avaliar', $pedido->id);

        $mensagem = $loja->wppTemplate('avaliacao');
        if (!$mensagem) {
            $mensagem = "⭐ *Como foi sua experiência?*\n"
                . str_repeat('─', 25) . "\n"
                . "Olá, *{$usuario->nome}*!\n\n"
                . "Seu pedido *{$pedido->numero}* foi entregue com sucesso!\n\n"
                . "Sua opinião é muito importante para nós.\n"
                . "Leva apenas 30 segundos para avaliar:\n\n"
                . "👉 {$link}\n\n"
                . "Obrigado por escolher a *{$loja->nome}*! 🙏";
        }

        $this->enviar($usuario->whatsapp, $mensagem);
    }

    public function verificarConexao(): bool
    {
        try {
            $resposta = Http::withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");
            return $resposta->successful() && ($resposta->json('instance.state') === 'open');
        } catch (Exception $e) {
            return false;
        }
    }
}
