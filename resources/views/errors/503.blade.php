<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja Fechada</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .container { text-align: center; padding: 40px 24px; max-width: 360px; }
        .icon { font-size: 5rem; margin-bottom: 16px; }
        h1 { font-size: 1.5rem; font-weight: 800; color: #2C3E50; margin: 0 0 8px; }
        p { color: #6c757d; margin: 0 0 24px; line-height: 1.6; }
        .horario { background: #fff; border-radius: 12px; padding: 16px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.08); font-size: 0.9rem; }
        a { display: inline-block; padding: 12px 24px; background: #FF6B35; color: #fff; border-radius: 24px; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🍔</div>
        <h1>Estamos fechados agora</h1>
        <p>A loja está temporariamente fechada ou em manutenção. Volte mais tarde!</p>
        @if(isset($loja))
        <div class="horario">
            <strong>Horário de Funcionamento</strong><br>
            @foreach($loja->horarios_funcionamento as $dia => $horario)
            @if($horario['ativo'] ?? false)
            <div>{{ ucfirst($dia) }}: {{ $horario['abre'] }} – {{ $horario['fecha'] }}</div>
            @endif
            @endforeach
        </div>
        @endif
        <a href="/">← Voltar ao Início</a>
    </div>
</body>
</html>
