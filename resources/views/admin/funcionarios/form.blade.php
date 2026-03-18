@extends('layouts.admin')
@section('titulo', isset($funcionario) ? 'Editar Funcionário' : 'Novo Funcionário')

@section('conteudo')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.funcionarios.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form action="{{ isset($funcionario) ? route('admin.funcionarios.update', $funcionario) : route('admin.funcionarios.store') }}" method="POST">
    @csrf
    @if(isset($funcionario)) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">
        <div>
            <div class="card-admin mb-3">
                <div class="card-admin-header"><h3><i class="bi bi-person"></i> Dados Pessoais</h3></div>
                <div class="card-admin-body">
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="campo-label">Nome Completo *</label>
                            <input type="text" name="nome" class="campo-input"
                                value="{{ old('nome', $funcionario?->usuario->nome) }}" required>
                            @error('nome')<span class="campo-erro">{{ $message }}</span>@enderror
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">E-mail *</label>
                            <input type="email" name="email" class="campo-input"
                                value="{{ old('email', $funcionario?->usuario->email) }}"
                                {{ isset($funcionario) ? 'readonly' : 'required' }}>
                            @error('email')<span class="campo-erro">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="campo-label">Telefone *</label>
                            <input type="text" name="telefone" class="campo-input mascara-telefone"
                                value="{{ old('telefone', $funcionario?->usuario->telefone) }}"
                                {{ isset($funcionario) ? '' : 'required' }}>
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">CPF</label>
                            <input type="text" name="cpf" class="campo-input mascara-cpf"
                                value="{{ old('cpf', $funcionario?->usuario->cpf) }}" placeholder="000.000.000-00">
                        </div>
                    </div>
                    @if(!isset($funcionario))
                    <div class="campo-grupo">
                        <label class="campo-label">Senha *</label>
                        <input type="password" name="senha" class="campo-input" required minlength="6" placeholder="Mínimo 6 caracteres">
                        @error('senha')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    @else
                    <div class="campo-grupo">
                        <label class="campo-label">Nova Senha (deixe vazio para manter)</label>
                        <input type="password" name="senha" class="campo-input" minlength="6" placeholder="Nova senha">
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-admin mb-3">
                <div class="card-admin-header"><h3><i class="bi bi-briefcase"></i> Cargo e Tipo</h3></div>
                <div class="card-admin-body">
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="campo-label">Tipo de Vínculo *</label>
                            <select name="tipo" class="campo-input" required>
                                @foreach(['funcionario' => 'Funcionário CLT', 'freelancer' => 'Freelancer', 'autonomo' => 'Autônomo', 'terceirizado' => 'Terceirizado'] as $val => $label)
                                <option value="{{ $val }}" {{ old('tipo', $funcionario?->tipo) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">Cargo / Título</label>
                            <input type="text" name="cargo" class="campo-input"
                                value="{{ old('cargo', $funcionario?->cargo) }}" placeholder="Ex: Gerente de Turno">
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input" name="e_entregador" id="eEntregador"
                                {{ old('e_entregador', $funcionario?->e_entregador) ? 'checked' : '' }}
                                onchange="toggleCamposEntregador(this.checked)">
                            <span class="switch-slider"></span>
                            <i class="bi bi-bicycle"></i> É entregador próprio
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-admin mb-3" id="secaoEntregador" style="{{ old('e_entregador', $funcionario?->e_entregador) ? '' : 'display:none' }}">
                <div class="card-admin-header"><h3><i class="bi bi-bicycle"></i> Dados do Entregador</h3></div>
                <div class="card-admin-body">
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="campo-label">Veículo</label>
                            <input type="text" name="veiculo" class="campo-input"
                                value="{{ old('veiculo', $funcionario?->veiculo) }}" placeholder="Ex: Moto Honda CG 160">
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">Placa</label>
                            <input type="text" name="placa_veiculo" class="campo-input"
                                value="{{ old('placa_veiculo', $funcionario?->placa_veiculo) }}" placeholder="ABC-1234">
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">CNH</label>
                        <input type="text" name="cnh" class="campo-input"
                            value="{{ old('cnh', $funcionario?->cnh) }}" placeholder="Número da CNH">
                    </div>
                </div>
            </div>
        </div>

        {{-- Lateral --}}
        <div>
            <div class="card-admin mb-3">
                <div class="card-admin-header"><h3><i class="bi bi-toggles"></i> Status</h3></div>
                <div class="card-admin-body">
                    <div class="campo-grupo">
                        <label class="switch-label">
                            <input type="checkbox" class="switch-input" name="ativo"
                                {{ old('ativo', $funcionario?->ativo ?? true) ? 'checked' : '' }}>
                            <span class="switch-slider"></span> Ativo
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">
                <i class="bi bi-check-lg"></i> Salvar
            </button>
            <a href="{{ route('admin.funcionarios.index') }}" class="btn btn-secondary w-100">Cancelar</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleCamposEntregador(show) {
    document.getElementById('secaoEntregador').style.display = show ? '' : 'none';
}
</script>
@endpush
