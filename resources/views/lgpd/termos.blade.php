@extends('layouts.pwa')
@section('titulo', 'Termos de Uso — ' . ($loja?->nome ?? config('app.name')))

@section('conteudo')
<div class="container py-4" style="max-width:720px;margin:0 auto;padding:0 16px">
    <div class="card-produto mb-4" style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06)">
        <h1 style="font-size:1.5rem;font-weight:900;color:var(--cor-secundaria);margin-bottom:4px">
            📄 Termos de Uso
        </h1>
        <p style="color:#6c757d;font-size:.85rem;margin-bottom:24px">
            {{ $loja?->nome ?? config('app.name') }} — Última atualização: {{ now()->format('d/m/Y') }}
        </p>

        <div style="line-height:1.8;color:#333;font-size:.92rem">
            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">1. Aceitação dos Termos</h2>
            <p>Ao acessar e utilizar nossa plataforma de pedidos online, você concorda com estes Termos de Uso. Caso não concorde, não utilize nossos serviços.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">2. Descrição do Serviço</h2>
            <p>{{ $loja?->nome ?? config('app.name') }} oferece um sistema de pedidos online que permite aos clientes realizar pedidos de alimentos e bebidas para entrega ou retirada no estabelecimento.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">3. Cadastro e Conta</h2>
            <p>Para realizar pedidos, o usuário deverá criar uma conta fornecendo informações verdadeiras, completas e atualizadas. O usuário é responsável pela confidencialidade de sua senha.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">4. Pedidos e Pagamentos</h2>
            <p>Ao realizar um pedido, o cliente confirma que as informações fornecidas são corretas. Os pagamentos são processados de forma segura. Pedidos cancelados após confirmação poderão estar sujeitos a políticas específicas de reembolso.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">5. Entrega</h2>
            <p>Os prazos de entrega são estimados e podem variar conforme demanda e condições externas. Não nos responsabilizamos por atrasos causados por fatores alheios ao nosso controle.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">6. Privacidade e LGPD</h2>
            <p>Seus dados pessoais são tratados conforme nossa <a href="{{ $loja?->lgpd_url_politica ?? route('lgpd.politica') }}" style="color:var(--cor-primaria)">Política de Privacidade</a>, em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018).</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">7. Responsabilidades</h2>
            <p>O estabelecimento não se responsabiliza por alergias ou restrições alimentares não informadas no pedido. É responsabilidade do cliente verificar os ingredientes dos produtos antes de solicitar.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">8. Propriedade Intelectual</h2>
            <p>Todo o conteúdo presente na plataforma (imagens, textos, logomarcas) é de propriedade do estabelecimento ou de seus fornecedores, sendo vedada a reprodução sem autorização prévia.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">9. Alterações</h2>
            <p>Reservamo-nos o direito de alterar estes termos a qualquer momento, sendo o uso continuado da plataforma considerado como aceitação das alterações.</p>

            <h2 style="font-size:1.1rem;font-weight:800;margin:20px 0 8px;color:var(--cor-secundaria)">10. Contato</h2>
            <p>Em caso de dúvidas sobre estes termos, entre em contato:
            @if($loja?->email) <strong>{{ $loja->email }}</strong> @endif
            @if($loja?->whatsapp) · WhatsApp: <strong>{{ $loja->whatsapp }}</strong> @endif
            </p>
        </div>

        <div style="margin-top:32px;padding-top:24px;border-top:1px solid #e9ecef;display:flex;gap:12px;flex-wrap:wrap">
            <a href="javascript:history.back()" class="btn btn-secundario">← Voltar</a>
            @if($loja?->lgpd_url_politica)
            <a href="{{ $loja->lgpd_url_politica }}" class="btn btn-primario">Política de Privacidade</a>
            @else
            <a href="{{ route('lgpd.politica') }}" class="btn btn-primario">Política de Privacidade</a>
            @endif
        </div>
    </div>
</div>
@endsection
