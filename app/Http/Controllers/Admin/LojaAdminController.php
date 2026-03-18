<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\BairroEntrega;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LojaAdminController extends Controller
{
    public function __construct(private UploadService $uploadService) {}

    public function index()
    {
        $lojas = Loja::withCount(['produtos', 'pedidos'])->paginate(20);
        return view('admin.lojas.index', compact('lojas'));
    }

    public function create()
    {
        $loja = null;
        return view('admin.lojas.form', compact('loja'));
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $dados['slug'] = Str::slug($dados['nome']);

        if ($request->hasFile('logo')) {
            $dados['logo'] = $this->uploadService->salvarImagemLoja($request->file('logo'), 'logo');
        } elseif ($request->filled('logo_path')) {
            $dados['logo'] = $request->logo_path;
        }
        if ($request->hasFile('banner')) {
            $dados['banner'] = $this->uploadService->salvarImagemLoja($request->file('banner'), 'banner');
        } elseif ($request->filled('banner_path')) {
            $dados['banner'] = $request->banner_path;
        }

        Loja::create($dados);
        return redirect()->route('admin.lojas.index')->with('sucesso', 'Loja criada com sucesso!');
    }

    public function edit(Loja $loja)
    {
        $loja->load(['bairrosEntrega']);
        return view('admin.lojas.form', compact('loja'));
    }

    public function update(Request $request, Loja $loja)
    {
        $dados = $this->validar($request, $loja->id);

        if ($request->hasFile('logo')) {
            $this->uploadService->deletar($loja->logo);
            $dados['logo'] = $this->uploadService->salvarImagemLoja($request->file('logo'), 'logo');
        } elseif ($request->filled('logo_path')) {
            $dados['logo'] = $request->logo_path;
        }
        if ($request->hasFile('banner')) {
            $this->uploadService->deletar($loja->banner);
            $dados['banner'] = $this->uploadService->salvarImagemLoja($request->file('banner'), 'banner');
        } elseif ($request->filled('banner_path')) {
            $dados['banner'] = $request->banner_path;
        }

        if ($request->horarios) {
            $dados['horarios_funcionamento'] = $request->horarios;
        }

        // Imagens dos pop-ups
        if ($request->filled('popup_saida_imagem')) {
            $dados['popup_saida_imagem'] = $request->popup_saida_imagem;
        }
        if ($request->filled('popup_promo_imagem')) {
            $dados['popup_promo_imagem'] = $request->popup_promo_imagem;
        }

        // Templates WhatsApp (array JSON)
        if ($request->has('wpp_templates')) {
            $dados['wpp_templates'] = array_filter($request->wpp_templates, fn($v) => !empty(trim($v)));
        }

        // Checkboxes que precisam ser false quando desmarcados
        $dados['popup_saida_ativo']     = $request->boolean('popup_saida_ativo');
        $dados['popup_promo_ativo']     = $request->boolean('popup_promo_ativo');
        $dados['cozinha_ativo']         = $request->boolean('cozinha_ativo');
        $dados['nfe_ativo']             = $request->boolean('nfe_ativo');
        $dados['notificacoes_whatsapp'] = $request->boolean('notificacoes_whatsapp');
        $dados['horario_automatico']    = $request->boolean('horario_automatico');
        $dados['ativo']                 = $request->boolean('ativo');
        $dados['aceita_retirada']       = $request->boolean('aceita_retirada');
        $dados['aceita_entrega']        = $request->boolean('aceita_entrega');
        $dados['aceita_pagamento_entrega'] = $request->boolean('aceita_pagamento_entrega');

        // Dias de funcionamento (array)
        if ($request->has('dias_funcionamento')) {
            $dados['dias_funcionamento'] = array_map('intval', $request->dias_funcionamento ?? []);
        }

        $loja->update($dados);
        return redirect()->back()->with('sucesso', 'Loja atualizada com sucesso!');
    }

    public function configuracoesMercadoPago(Request $request, Loja $loja)
    {
        $request->validate([
            'mercadopago_public_key'    => 'required|string',
            'mercadopago_access_token'  => 'required|string',
        ]);

        $loja->update([
            'mercadopago_public_key'    => $request->mercadopago_public_key,
            'mercadopago_access_token'  => $request->mercadopago_access_token,
        ]);

        return response()->json(['sucesso' => true, 'mensagem' => 'Credenciais MercadoPago salvas.']);
    }

    public function bairros(Loja $loja)
    {
        $bairros = BairroEntrega::where('loja_id', $loja->id)->get();
        return view('admin.lojas.bairros', compact('loja', 'bairros'));
    }

    public function salvarBairros(Request $request, Loja $loja)
    {
        $request->validate([
            'bairros'                    => 'required|array',
            'bairros.*.nome'             => 'required|string|max:100',
            'bairros.*.cidade'           => 'required|string|max:100',
            'bairros.*.taxa'             => 'required|numeric|min:0',
            'bairros.*.tempo_estimado_min'=> 'required|integer|min:0',
            'bairros.*.tempo_estimado_max'=> 'required|integer|min:0',
        ]);

        BairroEntrega::where('loja_id', $loja->id)->delete();

        foreach ($request->bairros as $b) {
            BairroEntrega::create(array_merge($b, ['loja_id' => $loja->id, 'estado' => $b['estado'] ?? 'SP']));
        }

        return response()->json(['sucesso' => true, 'mensagem' => 'Bairros salvos com sucesso.']);
    }

    public function uploadImagem(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|image|max:20480',
            'tipo'    => 'required|in:logo,banner,popup_saida,popup_promo',
        ]);
        $caminho = $this->uploadService->salvarImagemLoja($request->file('arquivo'), $request->tipo);
        return response()->json(['sucesso' => true, 'caminho' => $caminho, 'url' => asset('storage/' . $caminho)]);
    }

    private function validar(Request $request, ?int $ignorarId = null): array
    {
        // Validação customizada de CNPJ alfanumérico
        $request->validate([
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\Loja::validarCnpjAlfanumerico($value)) {
                        $fail('O CNPJ alfanumérico deve conter 14 caracteres (letras A-Z e números 0-9).');
                    }
                },
            ],
        ]);

        return $request->validate([
            'nome'                      => 'required|string|max:150',
            'cnpj'                      => 'nullable|string|max:18',
            'telefone'                  => 'nullable|string|max:20',
            'whatsapp'                  => 'nullable|string|max:20',
            'email'                     => 'nullable|email|max:150',
            'descricao'                 => 'nullable|string',
            'cep'                       => 'nullable|string|max:9',
            'logradouro'                => 'nullable|string|max:200',
            'numero'                    => 'nullable|string|max:20',
            'complemento'               => 'nullable|string|max:100',
            'bairro'                    => 'nullable|string|max:100',
            'cidade'                    => 'nullable|string|max:100',
            'estado'                    => 'nullable|string|max:2',
            'latitude'                  => 'nullable|numeric',
            'longitude'                 => 'nullable|numeric',
            'raio_entrega_km'           => 'nullable|numeric|min:0',
            'cor_primaria'              => 'nullable|string|max:7',
            'cor_secundaria'            => 'nullable|string|max:7',
            'pedido_minimo'             => 'nullable|numeric|min:0',
            'tempo_entrega_min'         => 'nullable|integer|min:0',
            'tempo_entrega_max'         => 'nullable|integer|min:0',
            'ativo'                     => 'boolean',
            'aceita_retirada'           => 'boolean',
            'aceita_entrega'            => 'boolean',
            'aceita_pagamento_entrega'  => 'boolean',
            'tipo_taxa_entrega'         => 'nullable|in:fixo,por_km,bairro,gratis',
            'taxa_entrega_fixa'         => 'nullable|numeric|min:0',
            'taxa_por_km'               => 'nullable|numeric|min:0',
            'km_gratis'                 => 'nullable|numeric|min:0',
            'chave_pix'                 => 'nullable|string|max:200',
            'evolution_instance'            => 'nullable|string|max:100',
            'notificacoes_whatsapp'         => 'boolean',
            // Pop-up saída
            'popup_saida_ativo'             => 'boolean',
            'popup_saida_titulo'            => 'nullable|string|max:200',
            'popup_saida_texto'             => 'nullable|string',
            'popup_saida_cupom'             => 'nullable|string|max:50',
            'popup_saida_desconto_tipo'     => 'nullable|in:percentual,fixo,frete_gratis',
            'popup_saida_desconto_valor'    => 'nullable|numeric|min:0',
            'popup_saida_validade_min'      => 'nullable|integer|min:1',
            // Pop-up promo
            'popup_promo_ativo'             => 'boolean',
            'popup_promo_titulo'            => 'nullable|string|max:200',
            'popup_promo_texto'             => 'nullable|string',
            'popup_promo_delay_seg'         => 'nullable|integer|min:0',
            'popup_promo_expira_em'         => 'nullable|date',
            'popup_promo_url'               => 'nullable|url|max:500',
            // LGPD
            'lgpd_texto_cookies'            => 'nullable|string',
            'lgpd_url_politica'             => 'nullable|url|max:500',
            'lgpd_url_termos'               => 'nullable|url|max:500',
            // Cozinha
            'cozinha_ativo'                 => 'boolean',
            'cozinha_pin'                   => 'nullable|string|max:20',
            // NFe
            'nfe_ativo'                     => 'boolean',
            'nfe_provedor'                  => 'nullable|string|max:50',
            'nfe_ambiente'                  => 'nullable|in:homologacao,producao',
            'nfe_token'                     => 'nullable|string|max:200',
            'nfe_cnpj_emitente'             => 'nullable|string|max:18',
            'nfe_razao_social'              => 'nullable|string|max:200',
            'nfe_serie'                     => 'nullable|string|max:10',
            'nfe_numero_atual'              => 'nullable|integer|min:1',
            // Horários automáticos
            'horario_automatico'            => 'boolean',
            'horario_abertura'              => 'nullable|date_format:H:i',
            'horario_fechamento'            => 'nullable|date_format:H:i',
            'dias_funcionamento'            => 'nullable|array',
            'dias_funcionamento.*'          => 'integer|min:0|max:6',
        ]);
    }
}
