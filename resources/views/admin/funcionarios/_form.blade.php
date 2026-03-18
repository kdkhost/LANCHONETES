<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

    {{-- Coluna principal --}}
    <div>
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-person"></i> Dados do Usuário</h3></div>
            <div class="card-admin-body">
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Nome Completo *</label>
                        <input type="text" name="nome" class="campo-input" value="{{ old('nome', $funcionario?->usuario->nome) }}" required placeholder="Nome do funcionário">
                        @error('nome')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">E-mail *</label>
                        <input type="email" name="email" class="campo-input" value="{{ old('email', $funcionario?->usuario->email) }}" required placeholder="funcionario@email.com">
                        @error('email')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Telefone</label>
                        <input type="text" name="telefone" class="campo-input mascara-telefone" value="{{ old('telefone', $funcionario?->usuario->telefone) }}" placeholder="(11) 98765-4321">
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">CPF</label>
                        <input type="text" name="cpf" class="campo-input mascara-cpf" value="{{ old('cpf', $funcionario?->usuario->cpf) }}" placeholder="000.000.000-00">
                    </div>
                </div>
                @if(!$funcionario)
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Senha *</label>
                        <input type="password" name="senha" class="campo-input" required placeholder="Mínimo 6 caracteres" minlength="6">
                        @error('senha')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Confirmar Senha *</label>
                        <input type="password" name="senha_confirmation" class="campo-input" required placeholder="Repita a senha">
                    </div>
                </div>
                @else
                <div class="campo-grupo">
                    <label class="campo-label">Nova Senha (deixe em branco para manter)</label>
                    <input type="password" name="senha" class="campo-input" placeholder="Nova senha" minlength="6">
                </div>
                @endif
            </div>
        </div>

        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-briefcase"></i> Cargo e Função</h3></div>
            <div class="card-admin-body">
                <div class="campo-row">
                    <div class="campo-grupo">
                        <label class="campo-label">Função no Sistema *</label>
                        <select name="role" class="campo-input" required>
                            <option value="gerente"    {{ old('role', $funcionario?->usuario->role) === 'gerente'    ? 'selected' : '' }}>Gerente</option>
                            <option value="atendente"  {{ old('role', $funcionario?->usuario->role) === 'atendente'  ? 'selected' : '' }}>Atendente</option>
                            <option value="cozinheiro" {{ old('role', $funcionario?->usuario->role) === 'cozinheiro' ? 'selected' : '' }}>Cozinheiro</option>
                            <option value="entregador" {{ old('role', $funcionario?->usuario->role) === 'entregador' ? 'selected' : '' }}>Entregador</option>
                        </select>
                        @error('role')<span class="campo-erro">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Cargo (título)</label>
                        <input type="text" name="cargo" class="campo-input" value="{{ old('cargo', $funcionario?->cargo) }}" placeholder="Ex: Gerente de Turno">
                    </div>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Data de Admissão</label>
                    <input type="date" name="data_admissao" class="campo-input" value="{{ old('data_admissao', $funcionario?->data_admissao?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>

        <div class="card-admin mb-3" id="secaoEntregador">
            <div class="card-admin-header"><h3><i class="bi bi-bicycle"></i> Dados do Entregador</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="e_entregador" id="eEntregador" {{ old('e_entregador', $funcionario?->e_entregador) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> É entregador próprio
                    </label>
                </div>
                <div id="camposEntregador">
                    <div class="campo-row">
                        <div class="campo-grupo">
                            <label class="campo-label">Veículo</label>
                            <input type="text" name="veiculo" class="campo-input" value="{{ old('veiculo', $funcionario?->veiculo) }}" placeholder="Ex: Moto Honda CG 160">
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label">Placa</label>
                            <input type="text" name="placa_veiculo" class="campo-input" value="{{ old('placa_veiculo', $funcionario?->placa_veiculo) }}" placeholder="ABC-1234">
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">CNH</label>
                        <input type="text" name="cnh" class="campo-input" value="{{ old('cnh', $funcionario?->cnh) }}" placeholder="Número da CNH">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Coluna lateral --}}
    <div>
        <div class="card-admin mb-3">
            <div class="card-admin-header"><h3><i class="bi bi-toggles"></i> Status</h3></div>
            <div class="card-admin-body">
                <div class="campo-grupo">
                    <label class="switch-label">
                        <input type="checkbox" class="switch-input" name="ativo" {{ old('ativo', $funcionario?->ativo ?? true) ? 'checked' : '' }}>
                        <span class="switch-slider"></span> Funcionário ativo
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class="bi bi-check-lg"></i> Salvar Funcionário
        </button>
        <a href="{{ route('admin.funcionarios.index') }}" class="btn btn-secondary w-100">Cancelar</a>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('select[name=role]')?.addEventListener('change', function() {
    const sec = document.getElementById('secaoEntregador');
    sec.style.display = this.value === 'entregador' ? '' : 'none';
});
// Init
const roleVal = document.querySelector('select[name=role]')?.value;
if (roleVal && roleVal !== 'entregador') {
    document.getElementById('secaoEntregador').style.display = 'none';
}
</script>
@endpush
