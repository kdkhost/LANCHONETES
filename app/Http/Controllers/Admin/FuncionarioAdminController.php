<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FuncionarioAdminController extends Controller
{
    public function index()
    {
        $lojaId      = Auth::user()->loja_id;
        $funcionarios = Funcionario::with('usuario')
            ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
            ->where('ativo', true)
            ->get();
        return view('admin.funcionarios.index', compact('funcionarios'));
    }

    public function create()
    {
        $funcionario = null;
        return view('admin.funcionarios.form', compact('funcionario'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'           => 'required|string|max:150',
            'email'          => 'required|email|unique:usuarios,email',
            'telefone'       => 'required|string|max:20',
            'cpf'            => 'nullable|string|max:14',
            'senha'          => 'required|string|min:6',
            'tipo'           => 'required|in:funcionario,freelancer,autonomo,terceirizado',
            'cargo'          => 'nullable|string|max:100',
            'e_entregador'   => 'boolean',
            'veiculo'        => 'nullable|string|max:100',
            'placa_veiculo'  => 'nullable|string|max:10',
            'cnh'            => 'nullable|string|max:20',
        ]);

        $lojaId = Auth::user()->loja_id;
        $role   = $request->boolean('e_entregador') ? 'entregador' : 'atendente';

        $usuario = Usuario::create([
            'nome'     => $request->nome,
            'email'    => $request->email,
            'telefone' => $request->telefone,
            'whatsapp' => $request->whatsapp ?? $request->telefone,
            'cpf'      => $request->cpf,
            'senha'    => $request->senha,
            'role'     => $role,
            'loja_id'  => $lojaId,
            'ativo'    => true,
        ]);

        Funcionario::create([
            'usuario_id'    => $usuario->id,
            'loja_id'       => $lojaId,
            'tipo'          => $request->tipo,
            'cargo'         => $request->cargo,
            'e_entregador'  => $request->boolean('e_entregador'),
            'veiculo'       => $request->veiculo,
            'placa_veiculo' => $request->placa_veiculo,
            'cnh'           => $request->cnh,
            'data_admissao' => now()->toDateString(),
            'ativo'         => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true, 'mensagem' => 'Funcionário cadastrado com sucesso!']);
        }
        return redirect()->route('admin.funcionarios.index')->with('sucesso', 'Funcionário cadastrado!');
    }

    public function edit(Funcionario $funcionario)
    {
        $funcionario->load('usuario');
        return view('admin.funcionarios.form', compact('funcionario'));
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $request->validate([
            'nome'          => 'required|string|max:150',
            'telefone'      => 'nullable|string|max:20',
            'tipo'          => 'required|in:funcionario,freelancer,autonomo,terceirizado',
            'cargo'         => 'nullable|string|max:100',
            'e_entregador'  => 'boolean',
            'veiculo'       => 'nullable|string|max:100',
            'placa_veiculo' => 'nullable|string|max:10',
            'cnh'           => 'nullable|string|max:20',
            'senha'         => 'nullable|string|min:6',
        ]);

        $dadosUsuario = [
            'nome'     => $request->nome,
            'telefone' => $request->telefone,
            'whatsapp' => $request->whatsapp ?? $request->telefone,
            'role'     => $request->boolean('e_entregador') ? 'entregador' : 'atendente',
        ];
        if ($request->filled('senha')) {
            $dadosUsuario['senha'] = $request->senha;
        }
        $funcionario->usuario->update($dadosUsuario);

        $funcionario->update($request->only(['tipo', 'cargo', 'e_entregador', 'veiculo', 'placa_veiculo', 'cnh']));

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true]);
        }
        return redirect()->route('admin.funcionarios.index')->with('sucesso', 'Funcionário atualizado!');
    }

    public function destroy(Funcionario $funcionario)
    {
        $funcionario->update(['ativo' => false]);
        $funcionario->usuario->update(['ativo' => false]);
        return response()->json(['sucesso' => true]);
    }
}
