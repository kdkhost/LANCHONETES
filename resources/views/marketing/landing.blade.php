@extends('layouts.marketing')
@section('titulo', 'Plataforma completa para franquias e delivery white-label')

@section('conteudo')
<section class="mk-hero">
    <div class="mk-container mk-hero__grid">
        <div>
            <p class="mk-pill">Nova geração de plataformas white-label</p>
            <h1>Transforme sua rede de lanchonetes em um ecossistema digital omnichannel.</h1>
            <p class="mk-lead">Centralize cardápios, pagamentos, logística e marketing em uma única experiência PWA com sua marca. Ideal para franquias que precisam de velocidade e governança.</p>
            <div class="mk-hero__cta">
                <a href="#planos" class="mk-btn">Ver planos</a>
                <a href="https://wa.me/5521981325441" class="mk-btn mk-btn--outline" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i> Agendar apresentação
                </a>
            </div>
            <div class="mk-metricas">
                <div>
                    <strong>+120</strong>
                    <span>lojas rodando em produção</span>
                </div>
                <div>
                    <strong>38%</strong>
                    <span>redução média no tempo de entrega</span>
                </div>
                <div>
                    <strong>62%</strong>
                    <span>clientes reativados via campanhas automáticas</span>
                </div>
            </div>
        </div>
        <div class="mk-hero__card">
            <img src="https://images.unsplash.com/photo-1523475472560-d2df97ec485c?auto=format&fit=crop&w=800&q=80" alt="Equipe monitorando dashboard">
            <div class="mk-hero__badge">
                <span><i class="bi bi-pie-chart"></i> Dashboard em tempo real</span>
                <span><i class="bi bi-geo-alt"></i> Rastreamento de entregas</span>
            </div>
        </div>
    </div>
</section>

<section id="beneficios" class="mk-section">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">Benefícios</p>
            <h2>Tudo o que sua operação precisa para escalar com consistência.</h2>
        </header>
        <div class="mk-grid mk-grid--4">
            @foreach($beneficios as $beneficio)
                <article class="mk-card">
                    <i class="bi {{ $beneficio['icon'] }}"></i>
                    <h3>{{ $beneficio['title'] }}</h3>
                    <p>{{ $beneficio['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section id="como-funciona" class="mk-section mk-section--alt">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">Como funciona</p>
            <h2>Onboarding guiado em três etapas.</h2>
        </header>
        <div class="mk-grid mk-grid--3">
            @foreach($comoFunciona as $etapa)
                <article class="mk-step">
                    <span>{{ $etapa['etapa'] }}</span>
                    <h3>{{ $etapa['titulo'] }}</h3>
                    <p>{{ $etapa['texto'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section id="planos" class="mk-section">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">Planos</p>
            <h2>Escolha o plano ideal para sua fase.</h2>
        </header>
        <div class="mk-grid mk-grid--3">
            @foreach($planos as $plano)
                <article class="mk-plan {{ $plano['destaque'] ?? false ? 'mk-plan--featured' : '' }}">
                    <div class="mk-plan__head">
                        <h3>{{ $plano['nome'] }}</h3>
                        <strong>{{ $plano['preco'] }}</strong>
                        <p>{{ $plano['descricao'] }}</p>
                    </div>
                    <ul>
                        @foreach($plano['recursos'] as $recurso)
                            <li><i class="bi bi-check-circle"></i> {{ $recurso }}</li>
                        @endforeach
                    </ul>
                    <a href="https://wa.me/5521981325441" target="_blank" class="mk-btn">Quero esse plano</a>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section id="cases" class="mk-section mk-section--alt">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">Cases reais</p>
            <h2>Redes que aceleraram com o Sistema Lanchonete.</h2>
        </header>
        <div class="mk-grid mk-grid--3">
            @foreach($cases as $case)
                <article class="mk-case">
                    <img src="{{ $case['logo'] }}" alt="{{ $case['titulo'] }}">
                    <h3>{{ $case['titulo'] }}</h3>
                    <p>{{ $case['texto'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="mk-section">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">Lojas demo</p>
            <h2>Experimente o PWA em diferentes contextos.</h2>
        </header>
        <div class="mk-demo-grid">
            @forelse($lojasDemo as $loja)
                <article class="mk-demo-card">
                    <div class="mk-demo-card__cover" style="--cover:url('{{ $loja->banner_url }}')"></div>
                    <div class="mk-demo-card__body">
                        <div class="mk-demo-card__logo">
                            <img src="{{ $loja->logo_url }}" alt="{{ $loja->nome }}">
                        </div>
                        <div>
                            <h3>{{ $loja->nome }}</h3>
                            <p>{{ $loja->cidade }}/{{ $loja->estado }}</p>
                            <p>{{ Str::limit($loja->descricao, 120) }}</p>
                        </div>
                        <a href="{{ route('cliente.loja', $loja->slug) }}" class="mk-btn mk-btn--outline" target="_blank">Abrir cardápio</a>
                    </div>
                </article>
            @empty
                <p>Execute o seeder demo para visualizar as lojas de exemplo.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="mk-section mk-section--alt">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">Avaliações</p>
            <h2>Depoimentos reais registrados no sistema.</h2>
        </header>
        <div class="mk-grid mk-grid--2">
            @forelse($avaliacoes as $avaliacao)
                <article class="mk-review">
                    <div class="mk-review__stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= ($avaliacao->nota_loja ?? 0) ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>
                    <p>“{{ Str::limit($avaliacao->comentario ?? 'Cliente elogiou a experiência completa.', 180) }}”</p>
                    <span>{{ $avaliacao->loja->nome ?? 'Loja Demo' }}</span>
                </article>
            @empty
                <p>Sem avaliações cadastradas ainda.</p>
            @endforelse
        </div>
    </div>
</section>

<section id="faq" class="mk-section">
    <div class="mk-container">
        <header class="mk-section__header">
            <p class="mk-pill">FAQ</p>
            <h2>Dúvidas frequentes.</h2>
        </header>
        <div class="mk-faq">
            @foreach($faq as $item)
                <details>
                    <summary>{{ $item['pergunta'] }}</summary>
                    <p>{{ $item['resposta'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

<section id="contato" class="mk-section mk-section--contact">
    <div class="mk-container mk-contact">
        <div>
            <p class="mk-pill">Contato</p>
            <h2>Fale com especialistas em expansão.</h2>
            <ul class="mk-contact__info">
                <li><i class="bi bi-telephone"></i> (21) 98132-5441</li>
                <li><i class="bi bi-envelope"></i> contato@kdkhost.com.br</li>
                <li><i class="bi bi-geo-alt"></i> Atendimento em todo o Brasil</li>
            </ul>
            <div class="mk-support-float">
                <a href="https://wa.me/5521981325441" target="_blank"><i class="bi bi-whatsapp"></i> Suporte imediato</a>
            </div>
        </div>
        <form action="{{ route('marketing.contato') }}" method="POST" class="mk-form">
            @csrf
            @if(session('sucesso'))
                <div class="mk-alert">{{ session('sucesso') }}</div>
            @endif
            <label>Nome completo
                <input type="text" name="nome" value="{{ old('nome') }}" required>
            </label>
            <label>Email corporativo
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Empresa/Franquia
                <input type="text" name="empresa" value="{{ old('empresa') }}">
            </label>
            <label>Como podemos ajudar?
                <textarea name="mensagem" rows="4" required>{{ old('mensagem') }}</textarea>
            </label>
            <button type="submit" class="mk-btn">Enviar mensagem</button>
        </form>
    </div>
</section>
@endsection
