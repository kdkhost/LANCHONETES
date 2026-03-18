<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MarketingController extends Controller
{
    public function landing()
    {
        $beneficios = [
            ['icon' => 'bi-stars',       'title' => 'Experiência White-label', 'text' => 'Personalize cores, domínio e comunicação para entregar sua própria marca de delivery.'],
            ['icon' => 'bi-lightning',   'title' => 'Automação Completa',       'text' => 'Pedidos, pagamentos, notificações WhatsApp e rastreamento em tempo real no mesmo painel.'],
            ['icon' => 'bi-graph-up',    'title' => 'Franquias e Multi Lojas',  'text' => 'Gerencie redes inteiras com dashboards unificados, permissões e planos sob medida.'],
            ['icon' => 'bi-shield-lock', 'title' => 'Infra Segura',             'text' => 'Hospedagem otimizada, backups automáticos e atualizações de segurança contínuas.'],
        ];

        $planos = [
            [
                'nome' => 'Starter',
                'preco' => 'R$ 129/mês',
                'descricao' => 'Ideal para quem está abrindo sua primeira operação digital.',
                'recursos' => ['01 loja ativa', 'Cardápio digital completo', 'Pagamentos PIX + Cartão', 'Suporte via WhatsApp em horário comercial'],
            ],
            [
                'nome' => 'Growth',
                'preco' => 'R$ 219/mês',
                'descricao' => 'Perfeito para franquias que precisam crescer rápido.',
                'recursos' => ['Até 05 lojas', 'Automação via Evolution API', 'Funil de vendas e campanhas', 'Suporte prioritário 7×7'],
                'destaque' => true,
            ],
            [
                'nome' => 'Enterprise',
                'preco' => 'Sob consulta',
                'descricao' => 'Projeto customizado com integrações exclusivas.',
                'recursos' => ['Lojas ilimitadas', 'SLA dedicado', 'Integração ERPs/BI', 'Consultoria de implantação'],
            ],
        ];

        $cases = [
            [
                'logo' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&w=200&h=200&q=80',
                'titulo' => 'Grupo Sabores Urbanos',
                'texto'  => '5 unidades migraram para o Sistema Lanchonete e reduziram 38% o tempo médio entre pedido e entrega.',
            ],
            [
                'logo' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=200&h=200&q=80',
                'titulo' => 'Rede Burger Express',
                'texto'  => 'A automação do Evolution API gerou +62% de reativação de clientes em 3 meses.',
            ],
            [
                'logo' => 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?auto=format&fit=crop&w=200&h=200&q=80',
                'titulo' => 'Chef em Casa Gourmet',
                'texto'  => 'Criação de operação 100% digital com cardápio sazonal e checkout próprio.',
            ],
        ];

        $faq = [
            ['pergunta' => 'Posso usar meu domínio e identidade visual?', 'resposta' => 'Sim! O painel permite configurar logo, cores, favicon e até apps PWA usando sua própria marca.'],
            ['pergunta' => 'Quais meios de pagamento são aceitos?', 'resposta' => 'PIX instantâneo, cartão de crédito/débito via Mercado Pago e pagamento na entrega conforme configuração da loja.'],
            ['pergunta' => 'Existe suporte para franquias?', 'resposta' => 'Planos Growth e Enterprise incluem multi-loja, permissões avançadas e dashboards consolidados.'],
            ['pergunta' => 'Como funciona a implantação?', 'resposta' => 'Após a assinatura, configuramos ambiente, importamos cardápio e treinamos sua equipe em até 5 dias úteis.'],
        ];

        $lojasDemo = Loja::where('ativo', true)
            ->select('id', 'nome', 'slug', 'logo', 'cidade', 'estado', 'descricao', 'cor_primaria')
            ->orderBy('nome')
            ->take(3)
            ->get();

        $avaliacoes = Avaliacao::with('loja')
            ->where('aprovado', true)
            ->latest()
            ->take(4)
            ->get();

        $comoFunciona = [
            ['etapa' => '1', 'titulo' => 'Configuração guiada', 'texto' => 'Importamos cardápio, criamos lojas demo e conectamos Mercado Pago + Evolution API.'],
            ['etapa' => '2', 'titulo' => 'Experiência PWA', 'texto' => 'Clientes usam aplicativo web com notificações push, rastreamento e checkout seguro.'],
            ['etapa' => '3', 'titulo' => 'Gestão em tempo real', 'texto' => 'Dashboard para franquias com metas, estatísticas e funil de reativação automática.'],
        ];

        return view('marketing.landing', compact('beneficios', 'planos', 'cases', 'faq', 'lojasDemo', 'avaliacoes', 'comoFunciona'));
    }

    public function contato(Request $request)
    {
        $dados = $request->validate([
            'nome'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:150'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'mensagem'=> ['required', 'string', 'max:2000'],
        ]);

        $destinatario = 'contato@kdkhost.com.br';

        try {
            Mail::raw(
                "Nova mensagem pelo site:\n" .
                "Nome: {$dados['nome']}\n" .
                "Email: {$dados['email']}\n" .
                "Empresa: " . ($dados['empresa'] ?? 'Não informado') . "\n" .
                "Mensagem:\n{$dados['mensagem']}",
                function ($mensagem) use ($destinatario) {
                    $mensagem->to($destinatario)->subject('Contato via Sistema Lanchonete');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar contato', ['erro' => $e->getMessage()]);
        }

        return back()->with('sucesso', 'Recebemos sua mensagem! Em breve retornaremos via e-mail ou WhatsApp.');
    }
}
