@extends('layouts.admin')
@section('titulo', 'Tours Guiados')

@section('conteudo')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1">🎓 Tours Guiados</h2>
        <p class="text-muted mb-0">Gerencie os tours interativos do sistema</p>
    </div>
    @if(Auth::user()->isSuperAdmin())
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoTourModal">
        <i class="bi bi-plus-circle"></i> Novo Tour
    </button>
    @endif
</div>

@if(Auth::user()->isSuperAdmin())
{{-- Modal Novo Tour --}}
<div class="modal fade" id="novoTourModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Novo Tour</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNovoTour">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Nome do Tour</label>
                                <input type="text" name="nome" class="form-control" required>
                                <small class="text-muted">Nome único para identificação (ex: primeiro_acesso)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" name="titulo" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Role Alvo</label>
                                <select name="target_role" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="admin">Admin</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="gerente">Gerente</option>
                                    <option value="atendente">Atendente</option>
                                    <option value="cozinheiro">Cozinheiro</option>
                                    <option value="entregador">Entregador</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Ordem</label>
                                <input type="number" name="ordem" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="ativo" id="tourAtivo" checked>
                                <label class="form-check-label" for="tourAtivo">
                                    Tour Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Passos do Tour</label>
                        <div id="passosContainer">
                            <div class="passo-item mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="text" placeholder="ID do Passo" name="passos[0][id]" class="form-control mb-2" required>
                                                <input type="text" placeholder="Seletor CSS" name="passos[0][element]" class="form-control mb-2" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" placeholder="Título" name="passos[0][title]" class="form-control mb-2" required>
                                                <input type="text" placeholder="Texto do Botão" name="passos[0][buttons][0][text]" class="form-control mb-2" value="Próximo">
                                            </div>
                                        </div>
                                        <textarea placeholder="Texto explicativo" name="passos[0][text]" class="form-control" rows="2" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarPasso()">
                            <i class="bi bi-plus"></i> Adicionar Passo
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Tour</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Tours Disponíveis --}}
<div class="row">
    @foreach($tours as $tour)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card-admin h-100">
            <div class="card-admin-header">
                <h3>{{ $tour->titulo }}</h3>
                <div class="d-flex gap-2">
                    @if($tour->ativo)
                    <span class="badge bg-success">Ativo</span>
                    @else
                    <span class="badge bg-secondary">Inativo</span>
                    @endif
                    @if($tour->target_role)
                    <span class="badge bg-info">{{ $tour->target_role }}</span>
                    @endif
                </div>
            </div>
            <div class="card-admin-body">
                <p class="text-muted">{{ $tour->descricao }}</p>
                
                <div class="tour-stats mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Passos:</span>
                        <strong>{{ count($tour->passos) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Concluídos:</span>
                        <strong>{{ $tour->usuarios_concluidos->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Pendentes:</span>
                        <strong>{{ $tour->usuarios_pendentes->count() }}</strong>
                    </div>
                </div>
                
                <div class="tour-progress mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progresso Geral</small>
                        <small>{{ $tour->usuarios->count() > 0 ? round(($tour->usuarios_concluidos->count() / $tour->usuarios->count()) * 100, 1) : 0 }}%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $tour->usuarios->count() > 0 ? round(($tour->usuarios_concluidos->count() / $tour->usuarios->count()) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="iniciarTour({{ $tour->id }})">
                        <i class="bi bi-play"></i> Testar
                    </button>
                    @if(Auth::user()->isSuperAdmin())
                    <button class="btn btn-sm btn-outline-secondary" onclick="editarTour({{ $tour->id }})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @if($tour->usuarios->count() === 0)
                    <button class="btn btn-sm btn-outline-danger" onclick="excluirTour({{ $tour->id }})">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($tours->isEmpty())
<div class="text-center py-5">
    <i class="bi bi-signpost-2 display-1 text-muted"></i>
    <h4 class="mt-3">Nenhum tour encontrado</h4>
    <p class="text-muted">Crie tours guiados para ajudar os usuários a conhecerem o sistema.</p>
    @if(Auth::user()->isSuperAdmin())
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#novoTourModal">
        <i class="bi bi-plus-circle"></i> Criar Primeiro Tour
    </button>
    @endif
</div>
@endif
@endsection

@push('scripts')
<script>
let passoCount = 1;

function adicionarPasso() {
    const container = document.getElementById('passosContainer');
    const passoHtml = `
        <div class="passo-item mb-3">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Passo ${passoCount + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.passo-item').remove()">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" placeholder="ID do Passo" name="passos[${passoCount}][id]" class="form-control mb-2" required>
                            <input type="text" placeholder="Seletor CSS" name="passos[${passoCount}][element]" class="form-control mb-2" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" placeholder="Título" name="passos[${passoCount}][title]" class="form-control mb-2" required>
                            <input type="text" placeholder="Texto do Botão" name="passos[${passoCount}][buttons][0][text]" class="form-control mb-2" value="Próximo">
                        </div>
                    </div>
                    <textarea placeholder="Texto explicativo" name="passos[${passoCount}][text]" class="form-control" rows="2" required></textarea>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', passoHtml);
    passoCount++;
}

function iniciarTour(tourId) {
    if (window.tourSystem) {
        window.tourSystem.iniciarTour(tourId);
    }
}

function editarTour(tourId) {
    // Implementar edição de tour
    alert('Funcionalidade de edição em desenvolvimento');
}

function excluirTour(tourId) {
    if (confirm('Tem certeza que deseja excluir este tour?')) {
        fetch(`/admin/tours/${tourId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao excluir tour');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir tour');
        });
    }
}

// Form submit
document.getElementById('formNovoTour')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    // Coletar dados básicos
    for (let [key, value] of formData.entries()) {
        if (!key.startsWith('passos[')) {
            data[key] = value;
        }
    }
    
    // Coletar passos
    data.passos = [];
    const passoInputs = formData.getAll('passos');
    
    // Organizar passos por índice
    const passosMap = {};
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('passos[')) {
            const match = key.match(/passos\[(\d+)\]\[(.+)\]/);
            if (match) {
                const index = match[1];
                const field = match[2];
                if (!passosMap[index]) passosMap[index] = {};
                passosMap[index][field] = value;
            }
        }
    }
    
    // Converter para array
    for (let index in passosMap) {
        data.passos.push(passosMap[index]);
    }
    
    // Enviar dados
    fetch('/admin/tours', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao criar tour');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao criar tour');
    });
});
</script>
@endpush
