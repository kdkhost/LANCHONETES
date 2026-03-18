<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    public function index()
    {
        $notificacoes = Auth::user()->notificacoes()->paginate(20);
        return view('cliente.notificacoes', compact('notificacoes'));
    }

    public function marcarLida(int $id)
    {
        $notif = Auth::user()->notificacoes()->findOrFail($id);
        $notif->marcarComoLida();
        return response()->json(['sucesso' => true]);
    }

    public function marcarTodasLidas()
    {
        Auth::user()->notificacoes()->where('lida', false)->update(['lida' => true, 'lida_em' => now()]);
        return response()->json(['sucesso' => true]);
    }
}
