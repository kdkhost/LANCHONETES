<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return $this->redirectPorRole(Auth::user());
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required|string',
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email'    => 'Informe um e-mail válido.',
            'senha.required' => 'A senha é obrigatória.',
        ]);

        $usuario = Usuario::where('email', $request->email)
            ->where('ativo', true)
            ->first();

        if (!$usuario || !Hash::check($request->senha, $usuario->senha)) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        Auth::login($usuario, $request->boolean('lembrar'));
        $usuario->update(['ultimo_acesso_em' => now()]);
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'sucesso'  => true,
                'redirect' => $this->getRedirectUrl($usuario),
                'usuario'  => [
                    'id'    => $usuario->id,
                    'nome'  => $usuario->nome,
                    'role'  => $usuario->role,
                    'foto'  => $usuario->foto_perfil_url,
                ],
            ]);
        }

        return redirect()->intended($this->getRedirectUrl($usuario));
    }

    public function showRegistro()
    {
        if (Auth::check()) return $this->redirectPorRole(Auth::user());
        return view('auth.registro');
    }

    public function registro(Request $request)
    {
        $request->validate([
            'nome'          => 'required|string|max:150',
            'email'         => 'required|email|unique:usuarios,email',
            'telefone'      => 'required|string|max:20',
            'cpf'           => 'nullable|string|max:14',
            'senha'         => 'required|string|min:6|confirmed',
        ], [
            'nome.required'     => 'O nome é obrigatório.',
            'email.required'    => 'O e-mail é obrigatório.',
            'email.unique'      => 'Este e-mail já está cadastrado.',
            'telefone.required' => 'O telefone é obrigatório.',
            'senha.required'    => 'A senha é obrigatória.',
            'senha.min'         => 'A senha deve ter no mínimo 6 caracteres.',
            'senha.confirmed'   => 'As senhas não conferem.',
        ]);

        $usuario = Usuario::create([
            'nome'      => $request->nome,
            'email'     => $request->email,
            'telefone'  => $request->telefone,
            'whatsapp'  => $request->whatsapp ?? $request->telefone,
            'cpf'       => $request->cpf,
            'senha'     => $request->senha,
            'role'      => 'cliente',
            'ativo'     => true,
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true, 'redirect' => route('cliente.home')]);
        }

        return redirect()->route('cliente.home')->with('sucesso', 'Bem-vindo(a), ' . $usuario->nome . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true]);
        }

        return redirect()->route('login');
    }

    private function redirectPorRole(Usuario $usuario)
    {
        return redirect($this->getRedirectUrl($usuario));
    }

    private function getRedirectUrl(Usuario $usuario): string
    {
        return match ($usuario->role) {
            'super_admin', 'admin', 'gerente' => route('admin.dashboard'),
            'atendente', 'cozinheiro'         => route('admin.pedidos.index'),
            'entregador'                      => route('entregador.dashboard'),
            default                           => route('cliente.home'),
        };
    }

    public function showEsqueceuSenha()
    {
        return view('auth.esqueceu-senha');
    }

    public function enviarRedefinicao(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $usuario = Usuario::where('email', $request->email)->first();
        if ($usuario) {
            $token = \Illuminate\Support\Str::random(64);
            $usuario->update([
                'token_redefinicao'            => Hash::make($token),
                'token_redefinicao_expira_em'  => now()->addHours(2),
            ]);

            $link = route('auth.redefinir-senha', ['token' => $token, 'email' => $usuario->email]);
            $usuario->notify(new \App\Notifications\RedefinirSenhaNotification($link));
        }

        return back()->with('info', 'Se o e-mail existir, você receberá um link de redefinição.');
    }

    public function showRedefinirSenha(Request $request)
    {
        return view('auth.redefinir-senha', ['token' => $request->token, 'email' => $request->email]);
    }

    public function redefinirSenha(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'senha' => 'required|min:6|confirmed',
        ]);

        $usuario = Usuario::where('email', $request->email)
            ->whereNotNull('token_redefinicao')
            ->where('token_redefinicao_expira_em', '>', now())
            ->first();

        if (!$usuario || !Hash::check($request->token, $usuario->token_redefinicao)) {
            return back()->withErrors(['token' => 'Token inválido ou expirado.']);
        }

        $usuario->update([
            'senha'                       => $request->senha,
            'token_redefinicao'           => null,
            'token_redefinicao_expira_em' => null,
        ]);

        return redirect()->route('login')->with('sucesso', 'Senha redefinida com sucesso!');
    }
}
