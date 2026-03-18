/* ================================================================
   SISTEMA LANCHONETE — JavaScript Admin
   Charset: UTF-8 — Português Brasileiro
   ================================================================ */

'use strict';

/* ── SIDEBAR ADMIN ────────────────────────────────────────────── */
function toggleAdminSidebar() {
    const sidebar  = document.getElementById('adminSidebar');
    const layout   = document.getElementById('adminLayout');
    const isMobile = window.innerWidth < 992;

    if (isMobile) {
        sidebar?.classList.toggle('ativo');
        if (sidebar?.classList.contains('ativo')) {
            const overlay = document.createElement('div');
            overlay.id    = 'adminOverlay';
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;backdrop-filter:blur(2px)';
            overlay.onclick = () => { sidebar.classList.remove('ativo'); overlay.remove(); };
            document.body.appendChild(overlay);
        } else {
            document.getElementById('adminOverlay')?.remove();
        }
    } else {
        layout?.classList.toggle('sidebar-collapsed');
        const isCollapsed = layout?.classList.contains('sidebar-collapsed');
        if (isCollapsed) {
            sidebar.style.width = 'var(--adm-sidebar-collapsed, 64px)';
            document.getElementById('adminContent').style.marginLeft = 'var(--adm-sidebar-collapsed, 64px)';
        } else {
            sidebar.style.width = 'var(--adm-sidebar-width, 240px)';
            document.getElementById('adminContent').style.marginLeft = 'var(--adm-sidebar-width, 240px)';
        }
        localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? '1' : '0');
    }
}

/* ── TOAST ADMIN ──────────────────────────────────────────────── */
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

/* ── AJAX ADMIN ───────────────────────────────────────────────── */
async function ajaxAdmin(url, dados = null, metodo = 'POST') {
    const opcoes = {
        method: metodo,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
        },
    };
    if (dados instanceof FormData) {
        opcoes.body = dados;
    } else if (dados) {
        opcoes.headers['Content-Type'] = 'application/json';
        opcoes.body = JSON.stringify(dados);
    }
    const res  = await fetch(url, opcoes);
    const json = await res.json();
    if (!res.ok) throw json;
    return json;
}

/* ── CONFIRMAR EXCLUSÃO ───────────────────────────────────────── */
function confirmarExclusao(url, mensagem = 'Deseja realmente excluir este item?') {
    if (!confirm(mensagem)) return;
    ajaxAdmin(url, null, 'DELETE')
        .then(data => {
            mostrarToast(data.mensagem || 'Excluído com sucesso.', 'sucesso');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch(err => mostrarToast(err.erro || 'Erro ao excluir.', 'erro'));
}

/* ── UPLOAD DRAG-DROP ADMIN ───────────────────────────────────── */
function inicializarUploadAdmin(selector, urlUpload, nomeInput, onSuccess) {
    document.querySelectorAll(selector).forEach(area => {
        const input    = area.querySelector('input[type=file]');
        const progress = area.closest('.campo-grupo')?.querySelector('.upload-progresso');

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
                if (!arquivo.type.startsWith('image/')) { mostrarToast('Somente imagens.', 'aviso'); return; }
                if (arquivo.size > 20 * 1024 * 1024) { mostrarToast('Arquivo muito grande (máx. 20MB).', 'aviso'); return; }
                enviarArquivo(arquivo);
            });
        }

        function enviarArquivo(arquivo) {
            const formData = new FormData();
            formData.append('arquivo', arquivo);
            if (area.dataset.tipo) formData.append('tipo', area.dataset.tipo);
            const xhr  = new XMLHttpRequest();
            const barra = progress?.querySelector('.upload-progresso-barra');
            const pctEl = progress?.querySelector('.upload-progresso-info span:last-child');
            const inicio= Date.now();

            xhr.open('POST', urlUpload);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            if (progress) progress.style.display = '';

            xhr.upload.addEventListener('progress', e => {
                if (!e.lengthComputable) return;
                const pct    = Math.round(e.loaded / e.total * 100);
                const elapsed= (Date.now() - inicio) / 1000;
                const rate   = e.loaded / elapsed;
                const restante = rate > 0 ? Math.round((e.total - e.loaded) / rate) : 0;
                if (barra) barra.style.width = pct + '%';
                if (pctEl) pctEl.textContent = pct + '% — ' + (restante > 0 ? restante + 's restantes' : 'Concluindo...');
            });

            xhr.addEventListener('load', () => {
                if (progress) setTimeout(() => { progress.style.display = 'none'; if (barra) barra.style.width = '0'; }, 1000);
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.sucesso) {
                        mostrarToast('Imagem enviada!', 'sucesso');
                        mostrarPreviewAdmin(area, resp.caminho, resp.url, nomeInput);
                        onSuccess?.(resp);
                    } else {
                        mostrarToast(resp.erro || 'Erro ao enviar.', 'erro');
                    }
                } catch { mostrarToast('Resposta inválida.', 'erro'); }
            });

            xhr.addEventListener('error', () => {
                mostrarToast('Falha no upload.', 'erro');
                if (progress) progress.style.display = 'none';
            });

            xhr.send(formData);
        }

        function mostrarPreviewAdmin(area, caminho, url, nome) {
            let preview = area.parentElement.querySelector('.upload-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'upload-preview';
                area.insertAdjacentElement('afterend', preview);
            }
            const item = document.createElement('div');
            item.className = 'upload-preview-item';
            item.dataset.caminho = caminho;
            item.innerHTML = `
                <img src="${url}" alt="preview">
                <button type="button" class="upload-preview-remove" onclick="this.closest('.upload-preview-item').remove()">
                    <i class="bi bi-x"></i>
                </button>
                <input type="hidden" name="${nome}" value="${caminho}">
            `;
            preview.appendChild(item);
        }
    });
}

/* ── ATUALIZAÇÃO DE STATUS DO PEDIDO ──────────────────────────── */
async function atualizarStatusPedido(pedidoId, novoStatus, observacao = '') {
    try {
        const data = await ajaxAdmin(`/admin/pedidos/${pedidoId}/status`, {
            status: novoStatus, observacao
        });
        mostrarToast(data.mensagem || 'Status atualizado!', 'sucesso');
        const badge = document.querySelector(`[data-pedido-status="${pedidoId}"]`);
        if (badge) badge.textContent = data.novo_status;
        return data;
    } catch (err) {
        mostrarToast(err.erro || 'Erro ao atualizar status.', 'erro');
        throw err;
    }
}

/* ── MÁSCARAS ─────────────────────────────────────────────────── */
function aplicarMascarasAdmin() {
    document.querySelectorAll('.mascara-cpf').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    });
    document.querySelectorAll('.mascara-cnpj').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 14);
            v = v.replace(/(\d{2})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1/$2').replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    });
    document.querySelectorAll('.mascara-telefone').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            v = v.length <= 10
                ? v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d{1,4})$/, '$1-$2')
                : v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d{1,4})$/, '$1-$2');
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
            if (!v) { this.value = ''; return; }
            v = (parseInt(v, 10) / 100).toFixed(2);
            this.value = v.replace('.', ',').replace(/(\d)(?=(\d{3})+,)/g, '$1.');
        });
    });
}

/* ── TOGGLE STATUS PRODUTO ────────────────────────────────────── */
async function toggleStatusProduto(produtoId, btn) {
    try {
        const data = await ajaxAdmin(`/admin/produtos/${produtoId}/toggle`, {});
        const ativo = data.ativo;
        btn.innerHTML  = ativo ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>';
        btn.title      = ativo ? 'Ativo — clique para desativar' : 'Inativo — clique para ativar';
        mostrarToast(ativo ? 'Produto ativado.' : 'Produto desativado.', 'info');
    } catch { mostrarToast('Erro ao alterar status.', 'erro'); }
}

/* ── KANBAN DRAG & DROP ───────────────────────────────────────── */
function inicializarKanban() {
    const cards = document.querySelectorAll('.kanban-card');
    const colunas = document.querySelectorAll('.kanban-items');

    cards.forEach(card => {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', e => {
            e.dataTransfer.setData('text/plain', card.dataset.pedidoId);
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', () => card.classList.remove('dragging'));
    });

    colunas.forEach(coluna => {
        coluna.addEventListener('dragover', e => { e.preventDefault(); coluna.classList.add('drag-over'); });
        coluna.addEventListener('dragleave', () => coluna.classList.remove('drag-over'));
        coluna.addEventListener('drop', async e => {
            e.preventDefault();
            coluna.classList.remove('drag-over');
            const pedidoId = e.dataTransfer.getData('text/plain');
            const novoStatus = coluna.closest('.kanban-coluna')?.dataset.status;
            if (!pedidoId || !novoStatus) return;
            const card = document.querySelector(`[data-pedido-id="${pedidoId}"]`);
            if (card) coluna.appendChild(card);
            await atualizarStatusPedido(pedidoId, novoStatus);
        });
    });
}

/* ── INICIALIZAÇÃO ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    aplicarMascarasAdmin();

    // Restaurar estado do sidebar
    const collapsed = localStorage.getItem('admin_sidebar_collapsed') === '1';
    if (collapsed && window.innerWidth >= 992) {
        const sidebar = document.getElementById('adminSidebar');
        const content = document.getElementById('adminContent');
        if (sidebar) sidebar.style.width = 'var(--adm-sidebar-collapsed, 64px)';
        if (content) content.style.marginLeft = 'var(--adm-sidebar-collapsed, 64px)';
    }

    // Inicializar uploads da área admin
    inicializarUploadAdmin('.upload-area[data-url]', '', 'imagem');

    // Kanban
    if (document.querySelector('.kanban-board')) inicializarKanban();

    // Alertas auto-fechar
    document.querySelectorAll('.alerta[data-auto]').forEach(el => {
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000);
    });

    // Atalho: Escape fecha modais
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop, .admin-modal').forEach(m => m.classList.remove('ativo'));
    });

    // Menu responsivo: adicionar toggle button handler
    document.getElementById('adminMenuToggle')?.addEventListener('click', toggleAdminSidebar);
});
