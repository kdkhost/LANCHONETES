<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu período de teste expirou</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #dc3545;">
        <h1 style="color: #dc3545; margin: 0;">
            <i style="font-size: 24px;">🔒</i> Seu Período de Teste Expirou
        </h1>
    </div>

    <!-- Content -->
    <div style="padding: 30px 0;">
        <p>Olá, <strong>{{ $loja->nome }}</strong>!</p>
        
        <p>Seu período de teste gratuito de 14 dias expirou.</p>
        
        <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>⚠️ Funcionalidades Bloqueadas:</strong></p>
            <ul style="margin: 10px 0 0 20px;">
                <li>Cadastro de novos produtos</li>
                <li>Configuração de gateway de pagamento</li>
                <li>Recebimento de novos pedidos</li>
            </ul>
        </div>
        
        <p>Mas não se preocupe! Seus dados estão seguros e você pode reativar sua loja a qualquer momento assinando um plano.</p>
        
        <p>Escolha o plano ideal para seu negócio:</p>
        
        <!-- Planos Comparison -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <div style="margin-bottom: 15px;">
                <h3 style="color: #28a745; margin: 0 0 5px 0;">🌟 Plano Profissional</h3>
                <p style="margin: 0; font-size: 18px; font-weight: bold;">R$ 97/mês ou R$ 970/ano (2 meses grátis)</p>
                <ul style="margin: 5px 0 0 20px; font-size: 14px;">
                    <li>✅ Produtos ilimitados</li>
                    <li>✅ Pagamento online</li>
                    <li>✅ Relatórios completos</li>
                    <li>✅ Suporte prioritário</li>
                    <li>✅ API e integrações</li>
                </ul>
            </div>
        </div>
        
        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $urlUpgrade }}" style="display: inline-block; background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                <i style="margin-right: 8px;">💳</i> Assinar Agora e Reactivar
            </a>
        </div>
        
        <p style="font-size: 14px; color: #666; margin-top: 30px;">
            Precisa de ajuda? Entre em contato com nossa equipe de suporte.
        </p>
    </div>

    <!-- Footer -->
    <div style="text-align: center; padding: 20px 0; border-top: 1px solid #e9ecef; color: #666; font-size: 12px;">
        <p style="margin: 0;">© {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
        <p style="margin: 5px 0 0;">
            <a href="{{ config('app.url') }}" style="color: #FF6B35; text-decoration: none;">{{ config('app.url') }}</a>
        </p>
    </div>

</body>
</html>
