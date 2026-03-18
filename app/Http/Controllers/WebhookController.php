<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        Log::info('Webhook MercadoPago recebido', $request->all());

        try {
            $dados  = $request->all();
            $lojaId = $request->header('X-Loja-Id');
            $loja   = $lojaId ? Loja::find($lojaId) : null;

            $mp = new MercadoPagoService($loja);
            $mp->processarWebhook($dados);
        } catch (\Exception $e) {
            Log::error('Webhook MercadoPago erro: ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function mercadoPagoPlano(Request $request)
    {
        Log::info('Webhook MercadoPago Plano recebido', $request->all());

        try {
            $dados = $request->all();
            
            // Para pagamentos de plano, não precisamos de loja específica no header
            // A referência externa contém as informações necessárias
            $mp = new MercadoPagoService(null);
            $resultado = $mp->processarWebhookPlano($dados);
            
            if ($resultado) {
                Log::info('Webhook plano processado com sucesso');
            } else {
                Log::warning('Webhook plano não processado');
            }
        } catch (\Exception $e) {
            Log::error('Webhook MercadoPago Plano erro: ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
