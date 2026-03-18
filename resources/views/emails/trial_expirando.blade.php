<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu período de teste expira em {{ $diasRestantes }} dias</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #FF6B35;">
        <h1 style="color: #FF6B35; margin: 0;">
            <i style="font-size: 24px;">⚠️</i> Atenção: Seu Trial Expira em Breve
        </h1>
    </div>

    <!-- Content -->
    <div style="padding: 30px 0;">
        <p>Olá, <strong>{{ $loja->nome }}</strong>!</p>
        
        <p>Seu período de teste gratuito da nossa plataforma expira em <strong style="color: #dc3545; font-size: 18px;">{{ $diasRestantes }} dias</strong>.</p>
        
        <p><strong>Data de expiração:</strong> {{ \Carbon\Carbon::parse($dataExpiracao)->format('d/m/Y') }}</p>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>O que acontece após o trial?</strong></p>
            <ul style="margin: 10px 0 0 20px;">
                <li>Você não poderá cadastrar novos produtos</li>
                <li>Configurações de gateway de pagamento serão bloqueadas</li>
                <li>Sua loja não poderá receber novos pedidos</li>
                <li>Dados existentes permanecerão intactos</li>
            </ul>
        </div>
        
        <p>Para continuar usando todos os recursos sem interrupção, assine um plano agora:</p>
        
        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $urlUpgrade }}" style="display: inline-block; background: linear-gradient(135deg, #FF6B35, #F7931E); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                <i style="margin-right: 8px;">🚀</i> Fazer Upgrade Agora
            </a>
        </div>
        
        <p style="font-size: 14px; color: #666; margin-top: 30px;">
            Se você tiver alguma dúvida, nossa equipe está à disposição para ajudar.
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
