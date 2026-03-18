<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start">

    {{-- Coluna principal --}}
    <div>
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-info-circle"></i> Informações do Produto</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="campo-label">Nome do Produto *</label>
                    <input type="text" name="nome" class="campo-input" value="{{ old('nome', $produto?->nome) }}" required placeholder="Ex: X-Burguer Especial">
                    @error('nome')<span class="campo-erro">{{ $message }}</span>@enderror
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Descrição</label>
                    <textarea name="descricao" class="campo-input" rows="3" placeholder="Descreva os ingredientes e diferenciais...">{{ old('descricao', $produto?->descricao) }}</textarea>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Categoria *</label>
                        <select name="categoria_id" class="campo-input" required>
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $produto?->categoria_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nome }}
                            </option>
                            @endforeach
                        </select>
                        @error('categoria_id')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Tempo de Preparo (min)</label>
                        <input type="number" name="tempo_preparo_min" class="campo-input" value="{{ old('tempo_preparo_min', $produto?->tempo_preparo_min ?? 15) }}" min="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Preços --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-currency-dollar"></i> Preço</h3></div>
            <div class="card-admin-body">
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Preço Normal (R$) *</label>
                        <input type="text" name="preco" class="campo-input mascara-moeda" value="{{ old('preco', $produto ? number_format($produto->preco, 2, ',', '.') : '') }}" required placeholder="0,00">
                        @error('preco')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Preço Promocional (R$)</label>
                        <input type="text" name="preco_promocional" class="campo-input mascara-moeda" value="{{ old('preco_promocional', $produto?->preco_promocional ? number_format($produto->preco_promocional, 2, ',', '.') : '') }}" placeholder="Deixe vazio se não houver">
                    </div>
                </div>
            </div>
        </div>

        {{-- Imagem --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-image"></i> Imagem</h3></div>
            <div class="card-admin-body">
                @if($produto?->imagem_principal)
                <div class="upload-preview mb-3" id="previewImagem">
                    <div class="upload-preview-item">
                        <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}">
                        <input type="hidden" name="imagem_path" value="{{ $produto->imagem_principal }}">
                    </div>
                </div>
                @else
                <div class="upload-preview mb-3" id="previewImagem"></div>
                @endif
                <div class="upload-area" data-url="{{ route('admin.produtos.upload') }}" id="uploadAreaProduto">
                    <input type="file" accept="image/*">
                    <div class="upload-area-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                    <p>Arraste a imagem ou <span>clique para selecionar</span></p>
                    <p><small>JPG, PNG ou WebP — máx. 20 MB</small></p>
                </div>
                <div class="upload-progresso"><div class="upload-progresso-barra"></div><div class="upload-progresso-info"><span>Enviando...</span><span>0%</span></div></div>
            </div>
        </div>

        {{-- Grupos de Adicionais --}}
        <div class="card-admin mb-3">
            <div class="card-admin-header">
                <h3><i class="bi bi-plus-square"></i> Grupos de Adicionais</h3>
                <button type="button" onclick="adicionarGrupo()" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Grupo</button>
            </div>
            <div class="card-admin-body" id="gruposContainer">
                @if($produto?->gruposAdicionais)
                @foreach($produto->gruposAdicionais as $gi => $grupo)
                <div class="grupo-adicional-item" data-grupo="{{ $gi }}">
                    <div class="grupo-header">
                        <span class="grupo-titulo">{{ $grupo->nome }}</span>
                        <button type="button" onclick="this.closest('.grupo-adicional-item').remove()" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                    </div>
                    <div class="campo-row">
                        <input type="hidden" name="grupos[{{ $gi }}][id]" value="{{ $grupo->id }}">
                        <div class="campo-grupo"><input type="text" name="grupos[{{ $gi }}][nome]" class="campo-input" value="{{ $grupo->nome }}" placeholder="Nome do grupo" required></div>
                    </div>
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="switch-label">
                                <input type="checkbox" class="switch-input" name="grupos[{{ $gi }}][obrigatorio]" {{ $grupo->obrigatorio ? 'checked' : '' }}>
                                <span class="switch-slider"></span> Obrigatório
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">Seleção máx.</label>
                            <input type="number" name="grupos[{{ $gi }}][max_selecao]" class="campo-input" value="{{ $grupo->max_selecao }}" min="1">
                        </div>
                    </div>
                    <div class="adicionais-lista" id="adicionais-{{ $gi }}">
                        @foreach($grupo->adicionais as $ai => $adic)
                        <div class="adicional-linha">
                            <input type="hidden" name="grupos[{{ $gi }}][adicionais][{{ $ai }}][id]" value="{{ $adic->id }}">
                            <input type="text" name="grupos[{{ $gi }}][adicionais][{{ $ai }}][nome]" class="campo-input" value="{{ $adic->nome }}" placeholder="Nome do adicional" style="flex:1">
                            <input type="text" name="grupos[{{ $gi }}][adicionais][{{ $ai }}][preco]" class="campo-input mascara-moeda" value="{{ number_format($adic->preco, 2, ',', '.') }}" placeholder="0,00" style="width:100px">
                            <button type="button" onclick="this.closest('.adicional-linha').remove()" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="adicionarAdicional({{ $gi }})" class="btn btn-sm btn-secondary mt-2">
                        <i class="bi bi-plus"></i> Adicional
                    </button>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Coluna lateral --}}
    <div>
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-toggles"></i> Configurações</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="ativo" {{ old('ativo', $produto?->ativo ?? true) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Produto ativo
                    </label>
                </div>
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="disponivel" {{ old('disponivel', $produto?->disponivel ?? true) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Disponível agora
                    </label>
                </div>
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="destaque" {{ old('destaque', $produto?->destaque) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Produto em destaque
                    </label>
                </div>
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="novo" {{ old('novo', $produto?->novo) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Sinalizar como "Novo"
                    </label>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Ordem de exibição</label>
                    <input type="number" name="ordem" class="campo-input" value="{{ old('ordem', $produto?->ordem ?? 0) }}" min="0">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class="bi bi-check-lg"></i> Salvar Produto
        </button>
        <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary w-100">Cancelar</a>
    </div>
</div>

<style>
.grupo-adicional-item { border: 1.5px solid var(--adm-borda); border-radius: var(--radius); padding: 12px; margin-bottom: 10px; background: var(--adm-fundo); }
.grupo-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.grupo-titulo { font-weight:700; font-size:.9rem; }
.adicional-linha { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
</style>

@push('scripts')
<script>
let grupoIdx = {{ $produto?->gruposAdicionais?->count() ?? 0 }};

function adicionarGrupo() {
    const idx = grupoIdx++;
    const html = `
    <div class="grupo-adicional-item" data-grupo="${idx}">
        <div class="grupo-header">
            <span class="grupo-titulo">Novo Grupo</span>
            <button type="button" onclick="this.closest('.grupo-adicional-item').remove()" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
        </div>
        <div class="campo-row">
            <div class="campo-grupo"><input type="text" name="grupos[${idx}][nome]" class="campo-input" placeholder="Ex: Adicionais, Ponto do hambúrguer..." required oninput="this.closest('.grupo-adicional-item').querySelector('.grupo-titulo').textContent=this.value||'Novo Grupo'"></div>
        </div>
        <div class="campo-row">
            <div class="campo-grupo">
                <label class="switch-label">
                    <input type="checkbox" class="switch-input" name="grupos[${idx}][obrigatorio]">
                    <span class="switch-slider"></span> Obrigatório
                </label>
            </div>
            <div class="campo-grupo">
                <label class="campo-label">Seleção máx.</label>
                <input type="number" name="grupos[${idx}][max_selecao]" class="campo-input" value="1" min="1">
            </div>
        </div>
        <div class="adicionais-lista" id="adicionais-${idx}"></div>
        <button type="button" onclick="adicionarAdicional(${idx})" class="btn btn-sm btn-secondary mt-2">
            <i class="bi bi-plus"></i> Adicional
        </button>
    </div>`;
    document.getElementById('gruposContainer').insertAdjacentHTML('beforeend', html);
}

function adicionarAdicional(grupoIdx) {
    const container = document.getElementById(`adicionais-${grupoIdx}`);
    const ai = container.querySelectorAll('.adicional-linha').length;
    container.insertAdjacentHTML('beforeend', `
        <div class="adicional-linha">
            <input type="text" name="grupos[${grupoIdx}][adicionais][${ai}][nome]" class="campo-input" placeholder="Ex: Queijo extra" style="flex:1">
            <input type="text" name="grupos[${grupoIdx}][adicionais][${ai}][preco]" class="campo-input mascara-moeda" placeholder="0,00" style="width:100px">
            <button type="button" onclick="this.closest('.adicional-linha').remove()" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
        </div>`);
    aplicarMascarasAdmin();
}

inicializarUploadAdmin('#uploadAreaProduto', '{{ route('admin.produtos.upload') }}', 'imagem_path', resp => {
    document.getElementById('previewImagem').innerHTML =
        `<div class="upload-preview-item"><img src="${resp.url}"><input type="hidden" name="imagem_path" value="${resp.caminho}"></div>`;
});
</script>
@endpush
