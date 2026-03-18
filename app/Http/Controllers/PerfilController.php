<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function __construct(private UploadService $uploadService) {}

    public function index()
    {
        $usuario   = Auth::user();
        $enderecos = $usuario->enderecos()->where('ativo', true)->get();
        return view('perfil.index', compact('usuario', 'enderecos'));
    }

    public function update(Request $request)
    {
        $usuario = Auth::user();
        $request->validate([
            'nome'      => 'required|string|max:150',
            'telefone'  => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:20',
            'cpf'       => 'nullable|string|max:14',
            'data_nascimento' => 'nullable|date',
            'genero'    => 'nullable|in:masculino,feminino,outro,prefiro_nao_informar',
            'senha_atual'   => 'nullable|required_with:nova_senha',
            'nova_senha'    => 'nullable|string|min:6|confirmed',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nova_senha.min'=> 'A nova senha deve ter no mínimo 6 caracteres.',
            'nova_senha.confirmed' => 'As senhas não conferem.',
        ]);

        if ($request->filled('nova_senha')) {
            if (!Hash::check($request->senha_atual, $usuario->senha)) {
                return response()->json(['erro' => 'Senha atual incorreta.'], 422);
            }
            $usuario->update(['senha' => $request->nova_senha]);
        }

        $usuario->update($request->only(['nome', 'telefone', 'whatsapp', 'cpf', 'data_nascimento', 'genero']));

        return response()->json(['sucesso' => true, 'mensagem' => 'Perfil atualizado com sucesso!']);
    }

    public function uploadFoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|max:5120']);
        $usuario = Auth::user();
        $this->uploadService->deletar($usuario->foto_perfil);
        $caminho = $this->uploadService->salvarFotoPerfil($request->file('foto'));
        $usuario->update(['foto_perfil' => $caminho]);
        return response()->json(['sucesso' => true, 'url' => asset('storage/' . $caminho)]);
    }

    public function enderecos()
    {
        $enderecos = Auth::user()->enderecos()->where('ativo', true)->get();
        return response()->json(['enderecos' => $enderecos]);
    }

    public function salvarEndereco(Request $request)
    {
        $request->validate([
            'apelido'    => 'nullable|string|max:50',
            'cep'        => 'required|string|max:9',
            'logradouro' => 'required|string|max:200',
            'numero'     => 'required|string|max:20',
            'complemento'=> 'nullable|string|max:100',
            'bairro'     => 'required|string|max:100',
            'cidade'     => 'required|string|max:100',
            'estado'     => 'required|string|max:2',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'principal'  => 'boolean',
        ]);

        $usuario = Auth::user();
        if ($request->boolean('principal')) {
            $usuario->enderecos()->update(['principal' => false]);
        }

        $endereco = $usuario->enderecos()->updateOrCreate(
            ['id' => $request->id],
            array_merge($request->except('id'), ['ativo' => true])
        );

        return response()->json(['sucesso' => true, 'endereco' => $endereco]);
    }

    public function removerEndereco(Endereco $endereco)
    {
        if ($endereco->usuario_id !== Auth::id()) abort(403);
        $endereco->update(['ativo' => false]);
        return response()->json(['sucesso' => true]);
    }

    public function definirPrincipal(Endereco $endereco)
    {
        if ($endereco->usuario_id !== Auth::id()) abort(403);
        Auth::user()->enderecos()->update(['principal' => false]);
        $endereco->update(['principal' => true]);
        return response()->json(['sucesso' => true]);
    }
}
