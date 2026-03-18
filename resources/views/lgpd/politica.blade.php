@extends('layouts.pwa')
@section('titulo', 'Política de Privacidade — ' . ($loja?->nome ?? config('app.name')))

@section('conteudo')
<div class="container py-4" style="max-width:720px;margin:0 auto;padding:0 16px">
    <div class="card-produto mb-4" style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06)">
        <h1 style="font-size:1.5rem;font-weight:900;color:var(--cor-secundaria);margin-bottom:4px">
            🔒 Política de Privacidade
        </h1>
        <p style="color:#6c757d;font-size:.85rem;margin-bottom:24px">
            {{ $loja?->nome ?? config('app.name') }} — Conforme a LGPD (Lei nº 13.709/2018)
        </p>

        <div style="line-height:1.8;color:#333;font-size:.92rem">
            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">1. Controlador dos Dados</h2>
            <p>{{ $loja?->nome ?? config('app.name') }}
            @if($loja?->cnpj) · CNPJ: {{ $loja->cnpj }} @endif
            @if($loja?->email) · E-mail: {{ $loja->email }} @endif
            </p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">2. Dados Coletados</h2>
            <p>Coletamos as seguintes informações para prestação dos nossos serviços:</p>
            <ul style="margin:8px 0 8px 20px">
                <li><strong>Dados de identificação:</strong> nome, e-mail, CPF (opcional), telefone/WhatsApp</li>
                <li><strong>Dados de localização:</strong> endereço de entrega fornecido pelo usuário</li>
                <li><strong>Dados de pagamento:</strong> processados por parceiros (Mercado Pago) — não armazenamos dados de cartão</li>
                <li><strong>Dados de navegação:</strong> cookies técnicos para funcionamento da plataforma</li>
                <li><strong>Histórico de pedidos:</strong> para facilitar novos pedidos e suporte</li>
            </ul>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">3. Finalidade do Tratamento</h2>
            <ul style="margin:8px 0 8px 20px">
                <li>Processamento e entrega de pedidos</li>
                <li>Comunicação sobre status do pedido via WhatsApp</li>
                <li>Melhorias na experiência do usuário</li>
                <li>Cumprimento de obrigações legais e fiscais</li>
                <li>Segurança e prevenção de fraudes</li>
            </ul>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">4. Base Legal</h2>
            <p>O tratamento de dados é realizado com base no <strong>consentimento</strong> (Art. 7º, I da LGPD), na <strong>execução de contrato</strong> (Art. 7º, V), e no <strong>legítimo interesse</strong> (Art. 7º, IX) do controlador.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">5. Compartilhamento de Dados</h2>
            <p>Seus dados podem ser compartilhados com:</p>
            <ul style="margin:8px 0 8px 20px">
                <li><strong>Entregadores:</strong> nome e endereço de entrega para cumprimento do pedido</li>
                <li><strong>Mercado Pago:</strong> para processamento de pagamentos</li>
                <li><strong>Evolution API:</strong> para envio de notificações via WhatsApp</li>
                <li><strong>Autoridades legais:</strong> quando exigido por lei</li>
            </ul>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">6. Retenção de Dados</h2>
            <p>Seus dados são mantidos pelo período necessário à prestação do serviço e cumprimento de obrigações legais (até 5 anos para dados fiscais).</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">7. Seus Direitos (LGPD Art. 18)</h2>
            <ul style="margin:8px 0 8px 20px">
                <li>Confirmação e acesso aos seus dados</li>
                <li>Correção de dados incompletos ou inexatos</li>
                <li>Anonimização, bloqueio ou eliminação</li>
                <li>Portabilidade dos dados</li>
                <li>Revogação do consentimento</li>
                <li>Oposição ao tratamento</li>
            </ul>
            <p>Para exercer seus direitos, entre em contato:
            @if($loja?->email) <strong>{{ $loja->email }}</strong> @endif</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">8. Cookies</h2>
            <p>Utilizamos cookies técnicos essenciais para o funcionamento da plataforma e cookies analíticos (com seu consentimento) para melhoria do serviço. Você pode gerenciar os cookies nas configurações do seu navegador.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">9. Segurança</h2>
            <p>Adotamos medidas técnicas e organizacionais adequadas para proteger seus dados contra acesso não autorizado, perda ou destruição.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">10. Contato e DPO</h2>
            <p>Para questões relacionadas à privacidade e proteção de dados:
            @if($loja?->email) <a href="mailto:{{ $loja->email }}" style="color:var(--cor-primaria)">{{ $loja->email }}</a> @endif
            @if($loja?->whatsapp) · WhatsApp: {{ $loja->whatsapp }} @endif
            </p>
        </div>

        <div style="margin-top:32px;padding-top:24px;border-top:1px solid #e9ecef;display:flex;gap:12px;flex-wrap:wrap">
            <a href="javascript:history.back()" class="btn btn-secundario">← Voltar</a>
            <a href="{{ route('lgpd.termos') }}" class="btn btn-primario">Termos de Uso</a>
        </div>
    </div>
</div>
@endsection
