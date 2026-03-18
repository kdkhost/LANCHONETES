<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Muitas Requisições</title>
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
        .countdown { font-size:2rem;font-weight:800;color:#FF6B35;margin:12px 0 24px }
        .btn { display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#FF6B35;color:#fff;border-radius:12px;text-decoration:none;font-weight:700;transition:.2s }
        .btn:hover { opacity:.9 }
    </style>
</head>
<body>
    <div class="erro-container">
        <div class="erro-icone"><i class="bi bi-shield-exclamation"></i></div>
        <div class="erro-codigo">429</div>
        <h1 class="erro-titulo">Muitas Tentativas</h1>
        <p class="erro-desc">Você fez muitas requisições em pouco tempo. Aguarde um momento antes de tentar novamente.</p>
        <div class="countdown" id="countdown">30</div>
        <a href="{{ url()->current() }}" class="btn" id="btnTentar">
            <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
        </a>
    </div>
    <script>
        let seg = 30;
        const el = document.getElementById('countdown');
        const btn = document.getElementById('btnTentar');
        btn.style.pointerEvents = 'none'; btn.style.opacity = '.5';
        const t = setInterval(() => {
            seg--;
            el.textContent = seg + 's';
            if (seg <= 0) {
                clearInterval(t);
                el.textContent = '✓';
                btn.style.pointerEvents = '';
                btn.style.opacity = '1';
            }
        }, 1000);
    </script>
</body>
</html>
