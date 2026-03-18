@extends('layouts.admin')
@section('titulo', 'Pedido #' . $pedido->numero)

@section('conteudo')
@php
    $cores  = config('lanchonete.pedido.cores_status');
    $labels = config('lanchonete.pedido.status');
    $cor    = $cores[$pedido->status] ?? '#6c757d';
@endphp

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <span class="badge-status" style="background:{{ $cor }}20;color:{{ $cor }};padding:6px 16px;border-radius:20px;font-size:.88rem;font-weight:700">
        {{ $labels[$pedido->status] ?? $pedido->status }}
    </span>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start">

    {{-- Coluna principal --}}
    <div>
        {{-- Itens --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-bag"></i> Itens do Pedido</h3></div>
            <div class="card-admin-body p-0">
                <table class="tabela-admin">
                    <thead>
                        <tr><th>Produto</th><th>Qtd</th><th>Unitário</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->itens as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->produto_nome }}</strong>
                                @foreach($item->adicionais as $a)
                                <small class="d-block text-muted">+ {{ $a->adicional_nome }} (R$ {{ number_format($a->preco, 2, ',', '.') }})</small>
                                @endforeach
                                @if($item->observacoes)
                                <small class="d-block text-info"><i class="bi bi-chat-text"></i> {{ $item->observacoes }}</small>
                                @endif
                            </td>
                            <td><strong>{{ $item->quantidade }}x</strong></td>
                            <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td><strong>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8f9fa">
                            <td colspan="3" class="text-right" style="text-align:right"><strong>Subtotal</strong></td>
                            <td><strong>R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</strong></td>
                        </tr>
                        @if($pedido->taxa_entrega > 0)
                        <tr style="background:#f8f9fa">
                            <td colspan="3" class="text-right" style="text-align:right">Taxa de Entrega</td>
                            <td>R$ {{ number_format($pedido->taxa_entrega, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($pedido->desconto > 0)
                        <tr style="background:#f8f9fa;color:#28a745">
                            <td colspan="3" class="text-right" style="text-align:right">Desconto ({{ $pedido->cupom_codigo }})</td>
                            <td>-R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr style="background:#fff3e0">
                            <td colspan="3" class="text-right" style="text-align:right;font-weight:800;font-size:1rem">TOTAL</td>
                            <td style="font-weight:800;font-size:1rem;color:#FF6B35">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Timeline de status --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-clock-history"></i> Histórico de Status</h3></div>
            <div class="card-admin-body">
                @forelse($pedido->historico_status ?? [] as $h)
                <div class="d-flex gap-3 mb-3">
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $cores[$h['status']] ?? '#6c757d' }};margin-top:5px;flex-shrink:0"></div>
                    <div>
                        <strong>{{ $labels[$h['status']] ?? $h['status'] }}</strong>
                        @if($h['observacao'] ?? null)<small class="d-block text-muted">{{ $h['observacao'] }}</small>@endif
                        <small class="text-muted">{{ \Carbon\Carbon::parse($h['data'])->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
                @empty
                <p class="text-muted small">Sem histórico registrado.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Coluna lateral --}}
    <div>
        {{-- Ações Rápidas --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-gear"></i> Ações</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="campo-label">Atualizar Status</label>
                    <select id="novoStatus" class="campo-input">
                        @foreach(config('lanchonete.pedido.status') as $chave => $label)
                        <option value="{{ $chave }}" {{ $pedido->status === $chave ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Observação (opcional)</label>
                    <textarea id="obsStatus" class="campo-input" rows="2" placeholder="Motivo da atualização..."></textarea>
                </div>
                <button onclick="salvarStatus()" class="btn btn-primary w-100" id="btnStatus">
                    <i class="bi bi-check-lg"></i> Atualizar Status
                </button>

                @if($pedido->status === 'pagamento_aprovado' || $pedido->status === 'confirmado')
                <hr>
                <div class="campo-grupo">
                    <label class="campo-label">Atribuir Entregador</label>
                    <select id="entregadorId" class="campo-input">
                        <option value="">Selecione...</option>
                        @foreach($entregadores ?? [] as $ent)
                        <option value="{{ $ent->id }}" {{ $pedido->entrega?->entregador_id === $ent->id ? 'selected' : '' }}>
                            {{ $ent->usuario->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button onclick="atribuirEntregador()" class="btn btn-outline-primary w-100">
                    <i class="bi bi-bicycle"></i> Atribuir Entregador
                </button>
                @endif

                <hr>
                <button onclick="enviarMensagem()" class="btn btn-secondary w-100">
                    <i class="bi bi-whatsapp"></i> Mensagem WhatsApp
                </button>
            </div>
        </div>

        {{-- Dados do Cliente --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-person"></i> Cliente</h3></div>
            <div class="card-admin-body">
                <div class="resumo-linha-adm"><span>Nome</span><strong>{{ $pedido->usuario->nome }}</strong></div>
                <div class="resumo-linha-adm"><span>Telefone</span><strong>{{ $pedido->usuario->telefone ?? '—' }}</strong></div>
                <div class="resumo-linha-adm"><span>E-mail</span><strong>{{ $pedido->usuario->email }}</strong></div>
            </div>
        </div>

        {{-- Pagamento --}}
        @if($pedido->pagamento)
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-credit-card"></i> Pagamento</h3></div>
            <div class="card-admin-body">
                <div class="resumo-linha-adm"><span>Método</span><strong>{{ $pedido->pagamento->metodo_label }}</strong></div>
                <div class="resumo-linha-adm"><span>Status</span>
                    <strong style="color:{{ $pedido->pagamento->status === 'aprovado' ? '#28a745' : ($pedido->pagamento->status === 'recusado' ? '#dc3545' : '#ffc107') }}">
                        {{ $pedido->pagamento->status_label }}
                    </strong>
                </div>
                @if($pedido->pagamento->mp_id)
                <div class="resumo-linha-adm"><span>ID MP</span><code>{{ $pedido->pagamento->mp_id }}</code></div>
                @endif
            </div>
        </div>
        @endif

        {{-- Endereço --}}
        @if($pedido->tipo_entrega === 'entrega')
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-geo-alt"></i> Endereço de Entrega</h3></div>
            <div class="card-admin-body">
                <p class="mb-0 small">
                    {{ $pedido->endereco_logradouro }}, {{ $pedido->endereco_numero }}
                    @if($pedido->endereco_complemento) — {{ $pedido->endereco_complemento }}@endif<br>
                    {{ $pedido->endereco_bairro }}, {{ $pedido->endereco_cidade }}/{{ $pedido->endereco_estado }}<br>
                    CEP: {{ $pedido->endereco_cep }}
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.resumo-linha-adm { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--adm-borda); font-size:.88rem; }
.resumo-linha-adm:last-child { border:none; }
.resumo-linha-adm span:first-child { color:var(--adm-muted); }
</style>
@endsection

@push('scripts')
<script>
async function salvarStatus() {
    const status = document.getElementById('novoStatus').value;
    const obs    = document.getElementById('obsStatus').value;
    const btn    = document.getElementById('btnStatus');
    btn.disabled = true; btn.textContent = 'Salvando...';
    try {
        const data = await ajaxAdmin('{{ route('admin.pedidos.status', $pedido) }}', { status, observacao: obs });
        mostrarToast('Status atualizado!', 'sucesso');
        setTimeout(() => location.reload(), 800);
    } catch(e) { mostrarToast(e.erro || 'Erro.', 'erro'); }
    finally { btn.disabled = false; btn.textContent = 'Atualizar Status'; }
}

async function atribuirEntregador() {
    const entregadorId = document.getElementById('entregadorId').value;
    if (!entregadorId) { mostrarToast('Selecione um entregador.', 'aviso'); return; }
    const data = await ajaxAdmin('{{ route('admin.pedidos.entregador', $pedido) }}', { entregador_id: entregadorId });
    mostrarToast(data.mensagem || 'Entregador atribuído!', 'sucesso');
    setTimeout(() => location.reload(), 800);
}

async function enviarMensagem() {
    const msg = prompt('Mensagem para enviar ao cliente via WhatsApp:');
    if (!msg) return;
    const data = await ajaxAdmin('{{ route('admin.pedidos.mensagem', $pedido) }}', { mensagem: msg });
    mostrarToast(data.mensagem || 'Mensagem enviada!', 'sucesso');
}
</script>
@endpush
