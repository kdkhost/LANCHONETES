@extends('layouts.admin')
@section('titulo', 'Bairros de Entrega — ' . $loja->nome)

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.lojas.edit', $loja) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar à Loja
    </a>
</div>

<form action="{{ route('admin.lojas.bairros.salvar', $loja) }}" method="POST" id="formBairros">
    @csrf
    <div class="card-admin mb-3">
        <div class="card-admin-header">
            <h3><i class="bi bi-geo-alt"></i> Bairros de Entrega</h3>
            <button type="button" onclick="adicionarBairro()" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Adicionar Bairro
            </button>
        </div>
        <div class="card-admin-body">
            <p class="text-muted small mb-3">Configure a taxa de entrega e tempo estimado por bairro. Deixe o campo Taxa vazio para usar a taxa padrão.</p>
            <div id="listaBairros">
                @foreach($bairros as $i => $bairro)
                <div class="bairro-linha" data-idx="{{ $i }}">
                    <div class="campo-row">
                        <div class="campo-grupo" style="flex:2">
                            <label class="campo-label">Nome do Bairro *</label>
                            <input type="text" name="bairros[{{ $i }}][nome]" class="campo-input"
                                value="{{ $bairro->nome }}" required placeholder="Ex: Centro">
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">Cidade</label>
                            <input type="text" name="bairros[{{ $i }}][cidade]" class="campo-input"
                                value="{{ $bairro->cidade ?? $loja->cidade }}" placeholder="{{ $loja->cidade }}">
                        </div>
                        <div class="campo-grupo" style="max-width:90px">
                            <label class="campo-label">UF</label>
                            <input type="text" name="bairros[{{ $i }}][estado]" class="campo-input"
                                value="{{ $bairro->estado ?? $loja->estado }}" maxlength="2" placeholder="{{ $loja->estado }}">
                        </div>
                    </div>
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="campo-label">Taxa de Entrega (R$)</label>
                            <input type="text" name="bairros[{{ $i }}][taxa]" class="campo-input mascara-moeda"
                                value="{{ $bairro->taxa ? number_format($bairro->taxa, 2, ',', '.') : '' }}" placeholder="0,00">
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">Tempo est. min./máx. (min)</label>
                            <div class="d-flex gap-2">
                            <input type="number" name="bairros[{{ $i }}][tempo_estimado_min]" class="campo-input"
                                value="{{ $bairro->tempo_estimado_min ?? 20 }}" placeholder="20" min="0">
                            <input type="number" name="bairros[{{ $i }}][tempo_estimado_max]" class="campo-input"
                                value="{{ $bairro->tempo_estimado_max ?? 40 }}" placeholder="40" min="0">
                            </div>
                        </div>
                        <div class="campo-grupo" style="align-self:flex-end;max-width:50px">
                            <button type="button" onclick="this.closest('.bairro-linha').remove()"
                                class="btn btn-sm btn-outline-danger w-100">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>
                    <hr>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Salvar Bairros
        </button>
        <a href="{{ route('admin.lojas.edit', $loja) }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<style>
.bairro-linha { margin-bottom: 4px; }
</style>
@endsection

@push('scripts')
<script>
let bairroIdx = {{ $bairros->count() }};

function adicionarBairro() {
    const idx = bairroIdx++;
    document.getElementById('listaBairros').insertAdjacentHTML('beforeend', `
        <div class="bairro-linha" data-idx="${idx}">
            <div class="campo-row">
                <div class="campo-grupo" style="flex:2">
                    <label class="campo-label">Nome do Bairro *</label>
                    <input type="text" name="bairros[${idx}][nome]" class="campo-input" required placeholder="Ex: Jardim das Flores">
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Cidade</label>
                    <input type="text" name="bairros[${idx}][cidade]" class="campo-input" placeholder="{{ $loja->cidade }}">
                </div>
                <div class="campo-grupo" style="max-width:90px">
                    <label class="campo-label">UF</label>
                    <input type="text" name="bairros[${idx}][estado]" class="campo-input" maxlength="2" placeholder="{{ $loja->estado }}">
                </div>
            </div>
            <div class="campo-row">
                <div class="campo-grupo">
                    <label class="campo-label">Taxa de Entrega (R$)</label>
                    <input type="text" name="bairros[${idx}][taxa]" class="campo-input mascara-moeda" placeholder="0,00">
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Tempo estimado (min)</label>
                    <div class="d-flex gap-2">
                    <input type="number" name="bairros[${idx}][tempo_estimado_min]" class="campo-input" placeholder="20" min="0">
                    <input type="number" name="bairros[${idx}][tempo_estimado_max]" class="campo-input" placeholder="40" min="0">
                    </div>
                </div>
                <div class="campo-grupo" style="align-self:flex-end;max-width:50px">
                    <button type="button" onclick="this.closest('.bairro-linha').remove()" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
            <hr>
        </div>`);
    aplicarMascarasAdmin();
}
</script>
@endpush
