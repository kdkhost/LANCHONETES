<?php

return [
    'nome_sistema'     => env('APP_NAME', 'Sistema Lanchonete'),
    'versao'           => '1.0.0',
    'moeda'            => 'BRL',
    'moeda_simbolo'    => 'R$',
    'pais'             => 'BR',
    'timezone'         => 'America/Sao_Paulo',
    'locale'           => 'pt_BR',

    'upload' => [
        'max_size_mb'     => 20,
        'imagens_produto' => 'produtos',
        'imagens_loja'    => 'lojas',
        'imagens_banner'  => 'banners',
        'imagens_perfil'  => 'perfis',
        'formatos'        => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'pedido' => [
        'status' => [
            'aguardando_pagamento' => 'Aguardando Pagamento',
            'pagamento_aprovado'   => 'Pagamento Aprovado',
            'confirmado'           => 'Confirmado',
            'em_preparo'           => 'Em Preparo',
            'pronto'               => 'Pronto',
            'saiu_para_entrega'    => 'Saiu para Entrega',
            'entregue'             => 'Entregue',
            'cancelado'            => 'Cancelado',
            'recusado'             => 'Recusado',
        ],
        'cores_status' => [
            'aguardando_pagamento' => '#FFC107',
            'pagamento_aprovado'   => '#17A2B8',
            'confirmado'           => '#007BFF',
            'em_preparo'           => '#6F42C1',
            'pronto'               => '#20C997',
            'saiu_para_entrega'    => '#FD7E14',
            'entregue'             => '#28A745',
            'cancelado'            => '#DC3545',
            'recusado'             => '#6C757D',
        ],
    ],

    'pagamento' => [
        'metodos' => [
            'pix'               => 'PIX',
            'cartao_credito'    => 'Cartão de Crédito',
            'cartao_debito'     => 'Cartão de Débito',
            'pagamento_entrega' => 'Pagamento na Entrega',
            'dinheiro'          => 'Dinheiro',
        ],
        'status' => [
            'pendente'   => 'Pendente',
            'aprovado'   => 'Aprovado',
            'recusado'   => 'Recusado',
            'cancelado'  => 'Cancelado',
            'reembolsado'=> 'Reembolsado',
            'em_analise' => 'Em Análise',
        ],
        'parcelas_maximas' => 12,
    ],

    'entrega' => [
        'tipos' => [
            'fixo'    => 'Taxa Fixa',
            'por_km'  => 'Por Quilômetro',
            'bairro'  => 'Por Bairro',
            'gratis'  => 'Grátis',
            'retirada'=> 'Retirada no Local',
        ],
    ],

    'roles' => [
        'super_admin' => 'Super Administrador',
        'admin'       => 'Administrador',
        'gerente'     => 'Gerente',
        'atendente'   => 'Atendente',
        'cozinheiro'  => 'Cozinheiro',
        'entregador'  => 'Entregador',
        'cliente'     => 'Cliente',
    ],

    'funcionario' => [
        'tipos' => [
            'funcionario'  => 'Funcionário CLT',
            'freelancer'   => 'Freelancer',
            'autonomo'     => 'Autônomo',
            'terceirizado' => 'Terceirizado',
        ],
    ],

    'notificacoes' => [
        'whatsapp' => [
            'pedido_novo'           => true,
            'pedido_confirmado'     => true,
            'pedido_em_preparo'     => true,
            'pedido_saiu_entrega'   => true,
            'pedido_entregue'       => true,
            'pedido_cancelado'      => true,
            'pagamento_aprovado'    => true,
            'pagamento_recusado'    => true,
            'link_rastreamento'     => true,
        ],
    ],

    'pwa' => [
        'nome'            => env('APP_NAME', 'Lanchonete'),
        'nome_curto'      => env('APP_NAME', 'Lanche'),
        'descricao'       => 'Peça sua comida favorita com facilidade',
        'cor_tema'        => '#FF6B35',
        'cor_fundo'       => '#FFFFFF',
        'orientacao'      => 'portrait',
        'display'         => 'standalone',
        'icone'           => '/img/icones/icon-512x512.png',
    ],

    'nfe' => [
        'provedores' => [
            'focusnfe' => [
                'nome'       => 'Focus NFe',
                'url_homolog'=> 'https://homologacao.focusnfe.com.br/v2',
                'url_prod'   => 'https://api.focusnfe.com.br/v2',
                'doc_url'    => 'https://developer.focusnfe.com.br',
            ],
        ],
        'status' => [
            'pendente'    => 'Pendente',
            'processando' => 'Processando',
            'autorizada'  => 'Autorizada',
            'cancelada'   => 'Cancelada',
            'rejeitada'   => 'Rejeitada',
            'denegada'    => 'Denegada',
        ],
        'cores_status' => [
            'pendente'    => '#6c757d',
            'processando' => '#ffc107',
            'autorizada'  => '#28a745',
            'cancelada'   => '#dc3545',
            'rejeitada'   => '#dc3545',
            'denegada'    => '#dc3545',
        ],
    ],

    'sounds' => [
        'novo_pedido' => '/sounds/novo-pedido.mp3',
        'pronto'      => '/sounds/pronto.mp3',
    ],

    'lgpd' => [
        'versao_termos'   => '1.0',
        'versao_politica' => '1.0',
    ],

    'popup' => [
        'desconto_tipos' => [
            'percentual'   => 'Percentual (%)',
            'fixo'         => 'Valor Fixo (R$)',
            'frete_gratis' => 'Frete Grátis',
        ],
    ],
];
