/* ================================================================
   SISTEMA LANCHONETE — JavaScript Principal
   Charset: UTF-8 — Português Brasileiro
   ================================================================ */

'use strict';

/* ── CARRINHO ─────────────────────────────────────────────────── */
const Carrinho = {
    CHAVE: 'carrinho_' + (window.LOJA_ID || '0'),

    obter() {
        try { return JSON.parse(localStorage.getItem(this.CHAVE)) || []; }
        catch { return []; }
    },

    salvar(itens) {
        localStorage.setItem(this.CHAVE, JSON.stringify(itens));
        this.atualizarBadges();
        this.atualizarPainel();
    },

    adicionar(produto) {
        const itens = this.obter();
        const chave = produto.produto_id + '_' + JSON.stringify(produto.adicionais || []);
        const idx   = itens.findIndex(i => i._chave === chave);

        if (idx >= 0) {
            itens[idx].quantidade += produto.quantidade || 1;
        } else {
            itens.push({ ...produto, _chave: chave, quantidade: produto.quantidade || 1 });
        }

        this.salvar(itens);
        this.animarAdicao();
        mostrarToast('Adicionado ao carrinho!', 'sucesso');
        return itens.length;
    },

    remover(chave) {
        const itens = this.obter().filter(i => i._chave !== chave);
        this.salvar(itens);
    },

    alterarQtd(chave, delta) {
        const itens = this.obter();
        const idx   = itens.findIndex(i => i._chave === chave);
        if (idx < 0) return;
        itens[idx].quantidade += delta;
        if (itens[idx].quantidade <= 0) itens.splice(idx, 1);
        this.salvar(itens);
    },

    limpar() {
        localStorage.removeItem(this.CHAVE);
        this.atualizarBadges();
        this.atualizarPainel();
    },

    total() {
        return this.obter().reduce((s, i) => s + (i.preco_total * i.quantidade), 0);
    },

    quantidade() {
        return this.obter().reduce((s, i) => s + i.quantidade, 0);
    },

    atualizarBadges() {
        const qtd = this.quantidade();
        const badges = [
            document.getElementById('badgeCarrinho'),
            document.getElementById('footerCarrinhoBadge'),
        ];
        badges.forEach(b => {
            if (!b) return;
            b.textContent = qtd;
            b.style.display = qtd > 0 ? 'flex' : 'none';
        });
    },

    atualizarPainel() {
        const itens    = this.obter();
        const vazio    = document.getElementById('carrinhoVazio');
        const footer   = document.getElementById('carrinhoFooter');
        const listaEl  = document.getElementById('carrinhoItens');
        const totalEl  = document.getElementById('carrinhoTotal');
        if (!listaEl) return;

        if (!itens.length) {
            if (vazio)  vazio.style.display = '';
            if (footer) footer.style.display = 'none';
            listaEl.innerHTML = '';
            if (vazio) listaEl.appendChild(vazio);
            return;
        }

        if (vazio)  vazio.style.display = 'none';
        if (footer) footer.style.display = '';

        const itensHtml = itens.map(item => `
            <div class="carrinho-item" data-chave="${item._chave}">
                <div class="carrinho-item-info">
                    <span class="carrinho-item-nome">${item.nome}</span>
                    ${item.adicionais?.length ? `<small class="text-muted">${item.adicionais.map(a => a.nome).join(', ')}</small>` : ''}
                </div>
                <div class="carrinho-item-controles">
                    <button class="qtd-btn" onclick="Carrinho.alterarQtd('${item._chave}', -1)">−</button>
                    <span class="qtd-valor">${item.quantidade}</span>
                    <button class="qtd-btn" onclick="Carrinho.alterarQtd('${item._chave}', 1)">+</button>
                </div>
                <span class="carrinho-item-preco">R$ ${(item.preco_total * item.quantidade).toFixed(2).replace('.', ',')}</span>
                <button class="carrinho-item-remove" onclick="Carrinho.remover('${item._chave}')">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        `).join('');

        listaEl.innerHTML = itensHtml;
        if (totalEl) totalEl.textContent = 'R$ ' + this.total().toFixed(2).replace('.', ',');
    },

    animarAdicao() {
        const btn = document.getElementById('footerCarrinhoBadge');
        if (!btn) return;
        btn.parentElement.classList.add('bounce');
        setTimeout(() => btn.parentElement.classList.remove('bounce'), 400);
    }
};

/* ── SIDEBAR ──────────────────────────────────────────────────── */
function abrirSidebar() {
    document.getElementById('sidebarLeft')?.classList.add('ativo');
    document.getElementById('sidebarOverlay')?.classList.add('ativo');
    document.body.style.overflow = 'hidden';
}

function fecharSidebar() {
    document.getElementById('sidebarLeft')?.classList.remove('ativo');
    document.getElementById('sidebarOverlay')?.classList.remove('ativo');
    document.body.style.overflow = '';
}

/* ── CARRINHO PANEL ───────────────────────────────────────────── */
function toggleCarrinho() {
    const panel = document.getElementById('carrinhoPanel');
    if (!panel) return;
    const aberto = panel.classList.contains('ativo');
    if (aberto) fecharCarrinho();
    else abrirCarrinho();
}

function abrirCarrinho() {
    document.getElementById('carrinhoPanel')?.classList.add('ativo');
    document.getElementById('carrinhoOverlay')?.classList.add('ativo');
    document.body.style.overflow = 'hidden';
    Carrinho.atualizarPainel();
}

function fecharCarrinho() {
    document.getElementById('carrinhoPanel')?.classList.remove('ativo');
    document.getElementById('carrinhoOverlay')?.classList.remove('ativo');
    document.body.style.overflow = '';
}

/* ── TOAST ────────────────────────────────────────────────────── */
function mostrarToast(mensagem, tipo = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icones = { sucesso: 'check-circle-fill', erro: 'x-circle-fill', info: 'info-circle-fill', aviso: 'exclamation-triangle-fill' };
    const toast  = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `<i class="bi bi-${icones[tipo] || 'info-circle-fill'}"></i> ${mensagem}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3100);
}

/* ── MÁSCARAS ─────────────────────────────────────────────────── */
function aplicarMascaras() {
    document.querySelectorAll('.mascara-cpf').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2')
                 .replace(/(\d{3})(\d)/, '$1.$2')
                 .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    });

    document.querySelectorAll('.mascara-cnpj').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 14);
            v = v.replace(/(\d{2})(\d)/, '$1.$2')
                 .replace(/(\d{3})(\d)/, '$1.$2')
                 .replace(/(\d{3})(\d)/, '$1/$2')
                 .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    });

    document.querySelectorAll('.mascara-telefone').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length <= 10) {
                v = v.replace(/(\d{2})(\d)/, '($1) $2')
                     .replace(/(\d{4})(\d{1,4})$/, '$1-$2');
            } else {
                v = v.replace(/(\d{2})(\d)/, '($1) $2')
                     .replace(/(\d{5})(\d{1,4})$/, '$1-$2');
            }
            this.value = v;
        });
    });

    document.querySelectorAll('.mascara-cep').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 8);
            v = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
            this.value = v;
        });
    });

    document.querySelectorAll('.mascara-moeda').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '');
            v = (parseInt(v, 10) / 100).toFixed(2);
            v = v.replace('.', ',').replace(/(\d)(?=(\d{3})+,)/g, '$1.');
            this.value = 'R$ ' + v;
        });
    });

    document.querySelectorAll('.mascara-data').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 8);
            v = v.replace(/(\d{2})(\d)/, '$1/$2')
                 .replace(/(\d{2})(\d)/, '$1/$2');
            this.value = v;
        });
    });
}

/* ── SENHA TOGGLE ─────────────────────────────────────────────── */
function toggleSenha(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const eh_senha = input.type === 'password';
    input.type     = eh_senha ? 'text' : 'password';
    btn.querySelector('i').className = `bi bi-eye${eh_senha ? '-slash' : ''}`;
}

/* ── ADICIONAR AO CARRINHO RÁPIDO ─────────────────────────────── */
function adicionarAoCarrinhoRapido(produtoId) {
    fetch(`${APP_URL}/api/produtos/${produtoId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.produto) {
            Carrinho.adicionar({
                produto_id:  data.produto.id,
                nome:        data.produto.nome,
                preco_total: data.produto.preco_atual,
                imagem:      data.produto.imagem_url,
                adicionais:  [],
            });
        }
    })
    .catch(() => mostrarToast('Erro ao adicionar produto', 'erro'));
}

/* ── SWIPE GESTURES (Sidebar) ─────────────────────────────────── */
function inicializarSwipe() {
    let startX = 0, startY = 0, dragging = false;

    document.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });

    document.addEventListener('touchend', e => {
        const endX = e.changedTouches[0].clientX;
        const endY = e.changedTouches[0].clientY;
        const diffX = endX - startX;
        const diffY = Math.abs(endY - startY);

        if (Math.abs(diffX) < 40 || diffY > 60) return;

        const sidebar = document.getElementById('sidebarLeft');
        if (diffX > 0 && startX < 30 && sidebar && !sidebar.classList.contains('ativo')) {
            abrirSidebar();
        } else if (diffX < 0 && sidebar?.classList.contains('ativo')) {
            fecharSidebar();
        }
    }, { passive: true });
}

/* ── SCROLL HEADER HIDE ───────────────────────────────────────── */
function inicializarScrollHeader() {
    const header = document.getElementById('pwaHeader');
    const footer = document.getElementById('footerNav');
    if (!header) return;

    let lastY = 0, ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const y = window.scrollY;
                if (y > lastY && y > 80) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
                lastY = y;
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
}

/* ── AJAX GLOBAL ──────────────────────────────────────────────── */
function ajaxPost(url, dados, opcoes = {}) {
    const headers = {
        'X-CSRF-TOKEN': CSRF_TOKEN,
        'X-Requested-With': 'XMLHttpRequest',
        ...opcoes.headers,
    };

    if (!(dados instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        dados = JSON.stringify(dados);
    }

    return fetch(url, {
        method: opcoes.method || 'POST',
        headers,
        body: dados,
    }).then(async r => {
        const json = await r.json();
        if (!r.ok) throw json;
        return json;
    });
}

/* ── UPLOAD DRAG-DROP ─────────────────────────────────────────── */
function inicializarUploadDragDrop(selector, opcoes = {}) {
    document.querySelectorAll(selector).forEach(area => {
        const input     = area.querySelector('input[type=file]');
        const progresso = area.nextElementSibling;
        const preview   = area.querySelector('.upload-preview') || area.parentElement.querySelector('.upload-preview');

        area.addEventListener('dragover',  e => { e.preventDefault(); area.classList.add('dragover'); });
        area.addEventListener('dragleave', () => area.classList.remove('dragover'));
        area.addEventListener('drop', e => {
            e.preventDefault();
            area.classList.remove('dragover');
            processarArquivos(Array.from(e.dataTransfer.files));
        });

        input?.addEventListener('change', () => processarArquivos(Array.from(input.files)));

        function processarArquivos(arquivos) {
            arquivos.forEach(arquivo => {
                if (!arquivo.type.startsWith('image/')) {
                    mostrarToast('Somente imagens são permitidas.', 'aviso');
                    return;
                }
                if (arquivo.size > 20 * 1024 * 1024) {
                    mostrarToast('Arquivo muito grande (máx. 20 MB).', 'aviso');
                    return;
                }
                enviarArquivo(arquivo);
            });
        }

        function enviarArquivo(arquivo) {
            const formData = new FormData();
            formData.append('arquivo', arquivo);
            if (opcoes.extra) {
                Object.entries(opcoes.extra).forEach(([k, v]) => formData.append(k, v));
            }

            if (progresso) progresso.style.display = '';
            const barra = progresso?.querySelector('.upload-progresso-barra');
            const info  = progresso?.querySelector('.upload-progresso-info span:last-child');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', opcoes.url || '/admin/produtos/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', e => {
                if (!e.lengthComputable) return;
                const pct = Math.round(e.loaded / e.total * 100);
                if (barra) barra.style.width = pct + '%';
                if (info)  info.textContent  = pct + '%';
            });

            xhr.addEventListener('load', () => {
                if (progresso) setTimeout(() => { progresso.style.display = 'none'; if (barra) barra.style.width = '0'; }, 800);
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.sucesso) {
                        mostrarToast('Imagem enviada!', 'sucesso');
                        if (preview) adicionarPreview(preview, resp.caminho, resp.url);
                        opcoes.onSuccess?.(resp);
                    } else {
                        mostrarToast(resp.erro || 'Erro no upload.', 'erro');
                    }
                } catch { mostrarToast('Resposta inválida.', 'erro'); }
            });

            xhr.addEventListener('error', () => {
                mostrarToast('Falha no upload.', 'erro');
                if (progresso) progresso.style.display = 'none';
            });

            xhr.send(formData);
        }

        function adicionarPreview(container, caminho, url) {
            const item = document.createElement('div');
            item.className = 'upload-preview-item';
            item.dataset.caminho = caminho;
            item.innerHTML = `
                <img src="${url}" alt="preview">
                <button type="button" class="upload-preview-remove" onclick="removerPreviewImagem(this)">
                    <i class="bi bi-x"></i>
                </button>
                <input type="hidden" name="${opcoes.nomeInput || 'imagem'}" value="${caminho}">
            `;
            container.appendChild(item);
        }
    });
}

function removerPreviewImagem(btn) {
    btn.closest('.upload-preview-item')?.remove();
}

/* ── ALERTAS AUTO-FECHAR ──────────────────────────────────────── */
function inicializarAlertas() {
    document.querySelectorAll('#alertaSucesso, [data-auto-fechar]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 3500);
    });
}

/* ── PWA INSTALL ──────────────────────────────────────────────── */
let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredInstallPrompt = e;
    const btnInstalar = document.getElementById('btnInstalarPwa');
    if (btnInstalar) btnInstalar.style.display = '';
});

function instalarPwa() {
    if (!deferredInstallPrompt) return;
    deferredInstallPrompt.prompt();
    deferredInstallPrompt.userChoice.then(result => {
        if (result.outcome === 'accepted') mostrarToast('Aplicativo instalado!', 'sucesso');
        deferredInstallPrompt = null;
    });
}

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    mostrarToast('App instalado com sucesso!', 'sucesso');
});

/* ── NOTIFICAÇÕES PUSH ────────────────────────────────────────── */
async function solicitarPermissaoNotificacao() {
    if (!('Notification' in window)) return;
    const permissao = await Notification.requestPermission();
    if (permissao === 'granted') {
        mostrarToast('Notificações ativadas!', 'sucesso');
    }
}

/* ── CONFIRMAÇÃO DESTRUIÇÃO ───────────────────────────────────── */
function confirmarAcao(mensagem, callback) {
    if (confirm(mensagem)) callback();
}

/* ── INICIALIZAÇÃO ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    Carrinho.atualizarBadges();
    Carrinho.atualizarPainel();
    aplicarMascaras();
    inicializarSwipe();
    inicializarScrollHeader();
    inicializarAlertas();

    // Fechar sidebar ao clicar em link
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) fecharSidebar();
        });
    });

    // Atalho de teclado: Escape fecha modais
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            fecharSidebar();
            fecharCarrinho();
            fecharModalProduto?.();
        }
    });

    // Inicializar áreas de upload globais
    inicializarUploadDragDrop('.upload-area-global');
});

/* ── ESTILOS DINÂMICOS PARA CARRINHO ITENS ────────────────────── */
const estiloCarrinho = document.createElement('style');
estiloCarrinho.textContent = `
.carrinho-item {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-bottom: 1px solid var(--cor-borda, #E8E8E8);
    font-size: 0.85rem;
}
.carrinho-item-info { flex: 1; min-width: 0; }
.carrinho-item-info small { display: block; color: var(--cor-texto-muted, #6C757D); font-size: 0.75rem; }
.carrinho-item-nome { font-weight: 700; display: block; }
.carrinho-item-controles { display: flex; align-items: center; gap: 6px; }
.qtd-btn {
    width: 26px; height: 26px; border-radius: 50%;
    border: 1.5px solid var(--cor-borda, #E8E8E8);
    background: var(--cor-fundo, #F5F5F5); cursor: pointer;
    font-size: 1rem; font-weight: 700; display: flex;
    align-items: center; justify-content: center;
    transition: all .15s ease;
}
.qtd-btn:hover { background: var(--cor-primaria, #FF6B35); color: #fff; border-color: var(--cor-primaria, #FF6B35); }
.qtd-valor { font-weight: 700; min-width: 20px; text-align: center; }
.carrinho-item-preco { font-weight: 800; white-space: nowrap; color: var(--cor-primaria, #FF6B35); }
.carrinho-item-remove { background: none; border: none; cursor: pointer; color: #dc3545; padding: 4px; border-radius: 4px; }
.bounce { animation: bounceKf .4s cubic-bezier(.34,1.56,.64,1); }
@keyframes bounceKf { 0%,100%{ transform:scale(1); } 50%{ transform:scale(1.3); } }
`;
document.head.appendChild(estiloCarrinho);
