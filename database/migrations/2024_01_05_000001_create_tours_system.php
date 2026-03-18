<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabela de Tours ─────────────────────────────────────────────────────
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100); // dashboard, produtos, pedidos, etc
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->json('passos'); // Array com os passos do tour
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->string('target_role', 50)->nullable(); // admin, super_admin, etc
            $table->timestamps();
            
            $table->index(['ativo', 'ordem']);
            $table->index('target_role');
        });

        // ── Tabela de Tours Completados por Usuário ───────────────────────────────────
        Schema::create('tour_usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tour_id');
            $table->enum('status', ['pendente', 'em_andamento', 'concluido', 'pulado'])->default('pendente');
            $table->integer('passo_atual')->default(0);
            $table->json('dados_adicionais')->nullable(); // Progresso, tempo, etc
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('tour_id')->references('id')->on('tours')->cascadeOnDelete();
            $table->unique(['user_id', 'tour_id']);
            $table->index(['user_id', 'status']);
        });

        // ── Inserir Tours Padrão ─────────────────────────────────────────────────────
        \DB::table('tours')->insert([
            [
                'nome' => 'primeiro_acesso_admin',
                'titulo' => 'Bem-vindo ao Painel Administrativo!',
                'descricao' => 'Tour guiado para conhecer as principais funcionalidades do sistema',
                'target_role' => 'admin',
                'ordem' => 1,
                'ativo' => true,
                'passos' => json_encode([
                    [
                        'id' => 'dashboard',
                        'element' => '.admin-nav-link[href="/admin/dashboard"]',
                        'title' => '🏠 Dashboard',
                        'text' => 'Este é seu painel principal! Aqui você verá um resumo das vendas, pedidos recentes e estatísticas importantes da sua loja.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'pedidos',
                        'element' => '.admin-nav-link[href*="pedidos"]',
                        'title' => '📋 Pedidos',
                        'text' => 'Aqui você gerencia todos os pedidos da sua loja. Visualize, atualize status, atribua entregadores e muito mais!',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'produtos',
                        'element' => '.admin-nav-link[href*="produtos"]',
                        'title' => '📦 Produtos',
                        'text' => 'Cadastre e gerencie seu cardápio aqui! Adicione produtos, categorias, preços e fotos.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'planos',
                        'element' => '.admin-nav-link[href*="planos"]',
                        'title' => '💳 Meu Plano',
                        'text' => 'Gerencie sua assinatura aqui. Veja seu plano atual, upgrade ou downgrade e histórico de pagamentos.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'relatorios',
                        'element' => '.admin-nav-link[href*="relatorios"]',
                        'title' => '📊 Relatórios',
                        'text' => 'Análise detalhada das suas vendas. Gere relatórios, exporte dados e acompanhe o desempenho.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'estatisticas',
                        'element' => '.admin-nav-link[href*="estatisticas"]',
                        'title' => '👁️ Visitas',
                        'text' => 'Veja estatísticas de visitas à sua loja. Saiba quais produtos são mais vistos e de onde vêm seus clientes.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'loja_config',
                        'element' => '.admin-nav-link[href*="lojas"]',
                        'title' => '⚙️ Configurações da Loja',
                        'text' => 'Personalize sua loja aqui! Configure dados, horários, delivery, pagamentos e muito mais.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'dashboard_content',
                        'element' => '.dashboard-cards',
                        'title' => '📈 Cards de Informações',
                        'text' => 'Estes cards mostram as informações mais importantes em tempo real: vendas do dia, pedidos pendentes, faturamento e mais.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'pedidos_recentes',
                        'element' => '.pedidos-recentes',
                        'title' => '📋 Pedidos Recentes',
                        'text' => 'Aqui você vê os últimos pedidos e pode agir rapidamente: aceitar, preparar para entrega ou cancelar.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'acoes_rapidas',
                        'element' => '.acoes-rapidas',
                        'title' => '🚀 Ações Rápidas',
                        'text' => 'Botões para ações comuns: cadastrar produto, ver pedidos, configurar delivery. Economize tempo!',
                        'buttons' => [
                            ['text' => 'Concluir Tour', 'action' => 'complete']
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'primeiro_produto',
                'title' => '📦 Cadastre seu Primeiro Produto',
                'descricao' => 'Aprenda a cadastrar produtos no seu cardápio',
                'target_role' => 'admin',
                'ordem' => 2,
                'ativo' => true,
                'passos' => json_encode([
                    [
                        'id' => 'btn_novo_produto',
                        'element' => '.btn-novo-produto',
                        'title' => '➕ Novo Produto',
                        'text' => 'Clique aqui para adicionar um novo produto ao seu cardápio. Vamos cadastrar seu primeiro item!',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'nome_produto',
                        'element' => 'input[name="nome"]',
                        'title' => '📝 Nome do Produto',
                        'text' => 'Dê um nome claro e apetitoso ao seu produto. Ex: "Hambúrguer X-Bacon" ou "Pizza Calabresa".',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'descricao_produto',
                        'element' => 'textarea[name="descricao"]',
                        'title' => '📄 Descrição',
                        'text' => 'Descreva seu produto detalhadamente. Ingredientes, modo de preparo, informações alérgicas, etc.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'preco_produto',
                        'element' => 'input[name="preco"]',
                        'title' => '💰 Preço',
                        'text' => 'Defina o preço do produto. Use vírgula para centavos: 29,90',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'categoria_produto',
                        'element' => 'select[name="categoria_id"]',
                        'title' => '📂 Categoria',
                        'text' => 'Selecione a categoria adequada. Isso ajuda os clientes a encontrarem seus produtos.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'imagem_produto',
                        'element' => '.upload-produto',
                        'title' => '📸 Foto do Produto',
                        'text' => 'Uma boa foto aumenta as vendas! Clique aqui para fazer upload da imagem do seu produto.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'disponibilidade',
                        'element' => 'input[name="disponivel"]',
                        'title' => '✅ Disponibilidade',
                        'text' => 'Marque se o produto está disponível para venda. Desmarque quando estiver em falta.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'btn_salvar',
                        'element' => '.btn-salvar-produto',
                        'title' => '💾 Salvar Produto',
                        'text' => 'Pronto! Clique aqui para salvar seu primeiro produto. Depois você pode cadastrar mais!',
                        'buttons' => [
                            ['text' => 'Concluir Tour', 'action' => 'complete']
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'primeiro_pedido',
                'title' => '📋 Gerenciando seu Primeiro Pedido',
                'descricao' => 'Aprenda a processar pedidos dos clientes',
                'target_role' => 'admin',
                'ordem' => 3,
                'ativo' => true,
                'passos' => json_encode([
                    [
                        'id' => 'lista_pedidos',
                        'element' => '.pedidos-table',
                        'title' => '📋 Lista de Pedidos',
                        'text' => 'Aqui você vê todos os pedidos recebidos. Cada linha mostra informações importantes do pedido.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'status_pedido',
                        'element' => '.status-badge',
                        'title' => '🔄 Status do Pedido',
                        'text' => 'Cores diferentes indicam status: azul (novo), verde (confirmado), laranja (preparando), etc.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'btn_detalhes',
                        'element' => '.btn-ver-pedido',
                        'title' => '👁️ Ver Detalhes',
                        'text' => 'Clique para ver todos os detalhes do pedido: itens, endereço, cliente, forma de pagamento.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'acoes_status',
                        'element' => '.acoes-status',
                        'title' => '⚡ Ações Rápidas',
                        'text' => 'Use estes botões para atualizar o status rapidamente: confirmar, preparar, entregar, etc.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'imprimir_pedido',
                        'element' => '.btn-imprimir',
                        'title' => '🖨️ Imprimir',
                        'text' => 'Imprima o pedido para a cozinha ou para controle interno. Formato otimizado para impressão.',
                        'buttons' => [
                            ['text' => 'Próximo', 'action' => 'next']
                        ]
                    ],
                    [
                        'id' => 'notificar_cliente',
                        'element' => '.btn-notificar',
                        'title' => '📱 Notificar Cliente',
                        'text' => 'Envie notificações automáticas por WhatsApp sobre o status do pedido.',
                        'buttons' => [
                            ['text' => 'Concluir Tour', 'action' => 'complete']
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_usuarios');
        Schema::dropIfExists('tours');
    }
};
