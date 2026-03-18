<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua assinatura foi ativada!</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #28a745;">
        <h1 style="color: #28a745; margin: 0;">
            <i style="font-size: 24px;">✅</i> Assinatura Ativada com Sucesso!
        </h1>
    </div>

    <!-- Content -->
    <div style="padding: 30px 0;">
        <p>Olá, <strong>{{ $assinatura->loja->nome }}</strong>!</p>
        
        <p>Parabéns! Sua assinatura foi ativada e você já pode aproveitar todos os recursos do plano <strong>{{ $assinatura->plano->nome }}</strong>.</p>
        
        <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>🎉 Detalhes da Assinatura:</strong></p>
            <ul style="margin: 10px 0 0 20px;">
                <li><strong>Plano:</strong> {{ $assinatura->plano->nome }}</li>
                <li><strong>Período:</strong> {{ ucfirst($assinatura->periodo) }}</li>
                <li><strong>Início:</strong> {{ $assinatura->data_inicio->format('d/m/Y') }}</li>
                <li><strong>Próxima cobrança:</strong> {{ $assinatura->data_fem->format('d/m/Y') }}</li>
                <li><strong>Valor:</strong> R$ {{ number_format($assinatura->valor_pago, 2, ',', '.') }}</li>
            </ul>
        </div>
        
        <p>Agora você tem acesso completo a:</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 14px;">
                <div><i style="color: #28a745;">✅</i> Produtos ilimitados</div>
                <div><i style="color: #28a745;">✅</i> Pagamento online</div>
                <div><i style="color: #28a745;">✅</i> Relatórios completos</div>
                <div><i style="color: #28a745;">✅</i> Estatísticas de visitas</div>
                <div><i style="color: #28a745;">✅</i> Notificações WhatsApp</div>
                <div><i style="color: #28a745;">✅</i> App Cozinha</div>
                <div><i style="color: #28a745;">✅</i> Integração NFe</div>
                <div><i style="color: #28a745;">✅</i> Suporte prioritário</div>
            </div>
        </div>
        
        <p>Sua loja está pronta para decolar! 🚀</p>
        
        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $urlPlanos }}" style="display: inline-block; background: linear-gradient(135deg, #007bff, #6610f2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                <i style="margin-right: 8px;">👤</i> Gerenciar Minha Assinatura
            </a>
        </div>
        
        <p style="font-size: 14px; color: #666; margin-top: 30px;">
            Se tiver qualquer dúvida, nossa equipe de suporte está à disposição.
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
