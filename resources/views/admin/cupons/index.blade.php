@extends('layouts.admin')
@section('titulo', 'Cupons de Desconto')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0">Gerencie os cupons de desconto da loja.</p>
    <button onclick="document.getElementById('modalNovoCupom').classList.add('ativo');document.getElementById('modalOverlayCupom').classList.add('ativo')" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Cupom
    </button>
</div>

<div class="card-admin">
    <div class="card-admin-body p-0">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Desconto</th>
                    <th>Uso Máx.</th>
                    <th>Usos</th>
                    <th>Válido até</th>
                    <th>Status</th>
                    <th style="width:100px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cupons as $cupom)
                <tr>
                    <td><code class="fw-bold">{{ $cupom->codigo }}</code></td>
                    <td>{{ $cupom->tipo === 'percentual' ? 'Percentual (%)' : 'Valor Fixo (R$)' }}</td>
                    <td>
                        <strong>
                            @if($cupom->tipo === 'percentual')
                            {{ $cupom->valor }}%
                            @else
                            R$ {{ number_format($cupom->valor, 2, ',', '.') }}
                            @endif
                        </strong>
                    </td>
                    <td>{{ $cupom->usos_maximos ?? '∞' }}</td>
                    <td>{{ $cupom->usos_realizados }}</td>
                    <td>
                        @if($cupom->valido_ate)
                        <span class="{{ $cupom->valido_ate->isPast() ? 'text-danger' : '' }}">
                            {{ $cupom->valido_ate->format('d/m/Y') }}
                        </span>
                        @else
                        <span class="text-muted">Sem validade</span>
                        @endif
                    </td>
                    <td>
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input toggle-cupom" data-id="{{ $cupom->id }}" {{ $cupom->ativo ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button onclick="editarCupom({{ $cupom->id }}, '{{ $cupom->codigo }}', '{{ $cupom->tipo }}', {{ $cupom->valor }}, {{ $cupom->usos_maximos ?? 'null' }}, '{{ $cupom->valido_ate?->format('Y-m-d') }}')"
                                class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></button>
                            <button onclick="confirmarExclusao('{{ route('admin.cupons.destroy', $cupom) }}', 'Excluir cupom {{ $cupom->codigo }}?')"
                                class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-tag fs-1 d-block mb-2"></i>
                        Nenhum cupom cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Cupom --}}
<div class="admin-modal-overlay" id="modalOverlayCupom" onclick="fecharModalCupom()"></div>
<div class="admin-modal" id="modalNovoCupom">
    <div class="admin-modal-header">
        <h3 id="modalCupomTitulo">Novo Cupom</h3>
        <button onclick="fecharModalCupom()" class="btn-fechar-modal"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="formCupom">
        @csrf
        <input type="hidden" id="cupomId" name="_cupom_id">
        <div class="campo-grupo">
            <label class="campo-label">Código *</label>
            <input type="text" name="codigo" id="cupomCodigo" class="campo-input" placeholder="Ex: DESCONTO10" required style="text-transform:uppercase">
        </div>
        <div class="campo-row">
            <div class="campo-grupo">
                <label class="campo-label">Tipo *</label>
                <select name="tipo" id="cupomTipo" class="campo-input" required>
                    <option value="percentual">Percentual (%)</option>
                    <option value="fixo">Valor Fixo (R$)</option>
                </select>
            </div>
            <div class="campo-grupo">
                <label class="campo-label">Valor *</label>
                <input type="number" name="valor" id="cupomValor" class="campo-input" step="0.01" min="0" required placeholder="Ex: 10">
            </div>
        </div>
        <div class="campo-row">
            <div class="campo-grupo">
                <label class="campo-label">Uso máximo</label>
                <input type="number" name="usos_maximos" id="cupomUsoMax" class="campo-input" min="1" placeholder="Ilimitado">
            </div>
            <div class="campo-grupo">
                <label class="campo-label">Válido até</label>
                <input type="date" name="valido_ate" id="cupomValidade" class="campo-input">
            </div>
        </div>
        <div class="campo-grupo">
            <label class="campo-label">Valor mínimo do pedido (R$)</label>
            <input type="number" name="valor_minimo_pedido" id="cupomMinimo" class="campo-input" step="0.01" min="0" placeholder="Sem mínimo">
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary" id="btnSalvarCupom">Salvar</button>
            <button type="button" onclick="fecharModalCupom()" class="btn btn-secondary">Cancelar</button>
        </div>
    </form>
</div>

<style>
.admin-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199;display:none; }
.admin-modal-overlay.ativo { display:block; }
.admin-modal { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#fff;border-radius:12px;padding:24px;width:95%;max-width:480px;z-index:200;box-shadow:0 8px 32px rgba(0,0,0,.2);display:none;transition:.2s;max-height:90vh;overflow-y:auto; }
.admin-modal.ativo { display:block;transform:translate(-50%,-50%) scale(1); }
.admin-modal-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:16px; }
.admin-modal-header h3 { font-size:1rem;font-weight:800;margin:0; }
.btn-fechar-modal { background:none;border:none;cursor:pointer;font-size:1.1rem;color:#6c757d; }
</style>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-cupom').forEach(cb => {
    cb.addEventListener('change', async function() {
        await ajaxAdmin(`/admin/cupons/${this.dataset.id}`, { ativo: this.checked }, 'PUT');
        mostrarToast('Cupom atualizado', 'sucesso');
    });
});
document.getElementById('cupomCodigo').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

function editarCupom(id, codigo, tipo, valor, usoMax, validade) {
    document.getElementById('cupomId').value   = id;
    document.getElementById('cupomCodigo').value = codigo;
    document.getElementById('cupomTipo').value   = tipo;
    document.getElementById('cupomValor').value  = valor;
    document.getElementById('cupomUsoMax').value = usoMax || '';
    document.getElementById('cupomValidade').value = validade || '';
    document.getElementById('modalCupomTitulo').textContent = 'Editar Cupom';
    document.getElementById('modalNovoCupom').classList.add('ativo');
    document.getElementById('modalOverlayCupom').classList.add('ativo');
}

function fecharModalCupom() {
    document.getElementById('modalNovoCupom').classList.remove('ativo');
    document.getElementById('modalOverlayCupom').classList.remove('ativo');
    document.getElementById('formCupom').reset();
    document.getElementById('cupomId').value = '';
    document.getElementById('modalCupomTitulo').textContent = 'Novo Cupom';
}

document.getElementById('formCupom').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('cupomId').value;
    const url = id ? `/admin/cupons/${id}` : '/admin/cupons';
    const btn = document.getElementById('btnSalvarCupom');
    btn.disabled = true; btn.textContent = 'Salvando...';
    const fd = new FormData(this);
    if (id) fd.append('_method', 'PUT');
    try {
        const res  = await fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.sucesso) { mostrarToast('Cupom salvo!', 'sucesso'); fecharModalCupom(); setTimeout(() => location.reload(), 700); }
        else mostrarToast(data.erro || 'Erro ao salvar.', 'erro');
    } catch { mostrarToast('Erro de conexão.', 'erro'); }
    finally { btn.disabled = false; btn.textContent = 'Salvar'; }
});
</script>
@endpush
