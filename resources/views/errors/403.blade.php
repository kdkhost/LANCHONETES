<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Acesso negado</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin:0;padding:0;box-sizing:border-box }
        body { font-family:'Nunito',sans-serif;background:#f8f9fa;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px }
        .erro-container { text-align:center;max-width:420px }
        .erro-icone { font-size:5rem;color:#ffc107;margin-bottom:16px }
        .erro-codigo { font-size:6rem;font-weight:800;color:#2C3E50;line-height:1 }
        .erro-titulo { font-size:1.4rem;font-weight:800;margin:12px 0 8px }
        .erro-desc { color:#6c757d;margin-bottom:24px;line-height:1.6 }
        .btns { display:flex;gap:12px;justify-content:center;flex-wrap:wrap }
        .btn-acao { display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;text-decoration:none;font-weight:700;transition:.2s }
        .btn-primario { background:#FF6B35;color:#fff; }
        .btn-secundario { background:#e9ecef;color:#495057; }
        .btn-acao:hover { opacity:.9 }
    </style>
</head>
<body>
    <div class="erro-container">
        <div class="erro-icone"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="erro-codigo">403</div>
        <h1 class="erro-titulo">Acesso não autorizado</h1>
        <p class="erro-desc">Você não tem permissão para acessar esta página. Faça login com uma conta que tenha as permissões necessárias.</p>
        <div class="btns">
            <a href="{{ url('/') }}" class="btn-acao btn-primario">
                <i class="bi bi-house-fill"></i> Início
            </a>
            <a href="{{ route('login') }}" class="btn-acao btn-secundario">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
        </div>
    </div>
</body>
</html>
