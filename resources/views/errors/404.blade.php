<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página não encontrada</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin:0;padding:0;box-sizing:border-box }
        body { font-family:'Nunito',sans-serif;background:#f8f9fa;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px }
        .erro-container { text-align:center;max-width:400px }
        .erro-icone { font-size:5rem;color:#FF6B35;margin-bottom:16px }
        .erro-codigo { font-size:6rem;font-weight:800;color:#2C3E50;line-height:1 }
        .erro-titulo { font-size:1.4rem;font-weight:800;margin:12px 0 8px }
        .erro-desc { color:#6c757d;margin-bottom:24px;line-height:1.6 }
        .btn-voltar { display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#FF6B35;color:#fff;border-radius:12px;text-decoration:none;font-weight:700;transition:.2s }
        .btn-voltar:hover { opacity:.9 }
    </style>
</head>
<body>
    <div class="erro-container">
        <div class="erro-icone"><i class="bi bi-search"></i></div>
        <div class="erro-codigo">404</div>
        <h1 class="erro-titulo">Página não encontrada</h1>
        <p class="erro-desc">A página que você está procurando não existe, foi movida ou está temporariamente indisponível.</p>
        <a href="{{ url('/') }}" class="btn-voltar">
            <i class="bi bi-house-fill"></i> Voltar ao início
        </a>
    </div>
</body>
</html>
