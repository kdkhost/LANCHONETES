<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua assinatura expira em {{ $diasRestantes }} dias</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #ffc107;">
        <h1 style="color: #ffc107; margin: 0;">
            <i style="font-size: 24px;">⚠️</i> Sua Assinatura Expira em Breve
        </h1>
    </div>

    <!-- Content -->
    <div style="padding: 30px 0;">
        <p>Olá, <strong>{{ $assinatura->loja->nome }}</strong>!</p>
        
        <p>Sua assinatura do plano <strong>{{ $assinatura->plano->nome }}</strong> expira em <strong style="color: #dc3545; font-size: 18px;">{{ $diasRestantes }} dias</strong>.</p>
        
        <p><strong>Data de expiração:</strong> {{ \Carbon\Carbon::parse($dataExpiracao)->format('d/m/Y') }}</p>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>O que acontece após a expiração?</strong></p>
            <ul style="margin: 10px 0 0 20px;">
                <li>Sua assinatura será cancelada automaticamente</li>
                <li>Funcionalidades premium serão bloqueadas</li>
                <li>Sua loja continuará operando com recursos básicos</li>
                <li>Dados existentes permanecerão intactos</li>
            </ul>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0;">Detalhes da Assinatura Atual:</h4>
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Plano:</strong> {{ $assinatura->plano->nome }}</li>
                <li><strong>Período:</strong> {{ ucfirst($assinatura->periodo) }}</li>
                <li><strong>Valor atual:</strong> R$ {{ number_format($assinatura->valor_pago, 2, ',', '.') }}</li>
                <li><strong>Início:</strong> {{ $assinatura->data_inicio->format('d/m/Y') }}</li>
                <li><strong>Próxima cobrança:</strong> {{ $assinatura->data_fem->format('d/m/Y') }}</li>
            </ul>
        </div>
        
        <p>Para evitar interrupções, renove sua assinatura agora:</p>
        
        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $urlUpgrade }}" style="display: inline-block; background: linear-gradient(135deg, #ffc107, #ff9800); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                <i style="margin-right: 8px;">🔄</i> Renovar Agora
            </a>
        </div>
        
        <p style="font-size: 14px; color: #666; margin-top: 30px;">
            Agradecemos sua confiança em nossa plataforma!
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
