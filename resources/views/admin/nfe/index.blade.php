@extends('layouts.admin')
@section('titulo', 'Notas Fiscais')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0">Notas fiscais emitidas pela loja.</p>
    <a href="{{ route('admin.lojas.edit', $loja) }}#nfe" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-gear"></i> Configurar NFe
    </a>
</div>

<div class="card-admin mb-4">
    <div class="card-admin-body">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="status" class="campo-input" style="width:auto">
                <option value="">Todos os status</option>
                @foreach(['pendente'=>'Pendente','processando'=>'Processando','autorizada'=>'Autorizada','cancelada'=>'Cancelada','rejeitada'=>'Rejeitada'] as $k=>$v)
                <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
            <input type="date" name="de"  value="{{ request('de') }}"  class="campo-input" style="width:auto" placeholder="De">
            <input type="date" name="ate" value="{{ request('ate') }}" class="campo-input" style="width:auto" placeholder="Até">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="table-admin">
            <thead>
                <tr>
                    <th>Nº NF</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Emitida em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notas as $nota)
                <tr>
                    <td><code>{{ $nota->numero ?? '—' }}</code></td>
                    <td><strong>{{ $nota->pedido->numero }}</strong></td>
                    <td>{{ $nota->pedido->usuario->nome }}</td>
                    <td>R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                    <td>
                        <span class="badge" style="background:{{ $nota->status_cor }}20;color:{{ $nota->status_cor }};border:1px solid {{ $nota->status_cor }};font-size:.75rem;padding:3px 10px;border-radius:20px;font-weight:700">
                            {{ $nota->status_label }}
                        </span>
                    </td>
                    <td>{{ $nota->emitida_em?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            @if($nota->url_danfe)
                            <a href="{{ route('admin.nfe.danfe', $nota) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver DANFE">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @endif
                            @if($nota->estaAutorizada())
                            <button onclick="cancelarNfe({{ $nota->id }})" class="btn btn-sm btn-outline-danger" title="Cancelar NF">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma nota fiscal emitida.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $notas->links() }}</div>
    </div>
</div>

{{-- Modal cancelamento --}}
<div class="modal-overlay" id="modalOverlayCancelNfe" style="display:none" onclick="fecharModalCancelNfe()"></div>
<div class="modal-custom" id="modalCancelNfe" style="display:none">
    <div class="modal-custom-header">
        <h3>Cancelar Nota Fiscal</h3>
        <button onclick="fecharModalCancelNfe()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-custom-body">
        <input type="hidden" id="cancelNfeId">
        <div class="campo-grupo">
            <label class="campo-label">Justificativa (mínimo 15 caracteres)</label>
            <textarea id="cancelJustificativa" class="campo-input" rows="3" placeholder="Descreva o motivo do cancelamento..."></textarea>
        </div>
    </div>
    <div class="modal-custom-footer">
        <button onclick="fecharModalCancelNfe()" class="btn btn-secondary">Cancelar</button>
        <button onclick="confirmarCancelNfe()" class="btn btn-danger">Cancelar NF</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
function cancelarNfe(id) {
    document.getElementById('cancelNfeId').value = id;
    document.getElementById('cancelJustificativa').value = '';
    document.getElementById('modalCancelNfe').style.display = 'block';
    document.getElementById('modalOverlayCancelNfe').style.display = 'block';
}
function fecharModalCancelNfe() {
    document.getElementById('modalCancelNfe').style.display = 'none';
    document.getElementById('modalOverlayCancelNfe').style.display = 'none';
}
async function confirmarCancelNfe() {
    const id = document.getElementById('cancelNfeId').value;
    const just = document.getElementById('cancelJustificativa').value.trim();
    if (just.length < 15) { alert('Justificativa muito curta (mín 15 caracteres).'); return; }

    const r = await fetch(`/admin/nfe/${id}/cancelar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ justificativa: just })
    });
    const data = await r.json();
    if (data.sucesso) { location.reload(); }
    else { alert(data.erro || 'Erro ao cancelar.'); }
}
</script>
@endpush
