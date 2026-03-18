<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cozinha — Acesso</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0;padding:0;box-sizing:border-box }
        body { font-family:'Nunito',sans-serif;background:#1a1a2e;display:flex;align-items:center;justify-content:center;min-height:100vh }
        .pin-box { background:#16213e;border-radius:20px;padding:48px 40px;text-align:center;width:340px;box-shadow:0 20px 60px rgba(0,0,0,.5) }
        .pin-icon { font-size:3.5rem;margin-bottom:16px }
        h1 { font-size:1.6rem;font-weight:900;color:#fff;margin-bottom:4px }
        .sub { color:#8892b0;font-size:.9rem;margin-bottom:32px }
        .pin-input { width:100%;padding:16px;font-size:2rem;text-align:center;letter-spacing:12px;border:2px solid #2d3561;border-radius:12px;background:#0f3460;color:#fff;font-family:inherit;font-weight:900;outline:none;transition:.2s }
        .pin-input:focus { border-color:#FF6B35 }
        .btn { width:100%;margin-top:16px;padding:14px;background:#FF6B35;color:#fff;border:none;border-radius:12px;font-size:1.1rem;font-weight:800;font-family:inherit;cursor:pointer;transition:.2s }
        .btn:hover { background:#e55a25 }
        .erro { color:#ff6b6b;font-size:.85rem;margin-top:12px }
        .loja-nome { font-size:.8rem;color:#8892b0;margin-top:24px }
    </style>
</head>
<body>
    <div class="pin-box">
        <div class="pin-icon">👨‍🍳</div>
        <h1>Tela da Cozinha</h1>
        <p class="sub">{{ $loja->nome }}</p>

        <form method="POST" action="{{ route('admin.cozinha.pin') }}">
            @csrf
            <input type="password" name="pin" class="pin-input" placeholder="••••" maxlength="10" autofocus>
            @error('pin') <p class="erro">{{ $message }}</p> @enderror
            <button type="submit" class="btn">🔓 Entrar</button>
        </form>

        <p class="loja-nome">Acesso restrito à equipe de cozinha</p>
    </div>
</body>
</html>
