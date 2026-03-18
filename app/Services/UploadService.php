<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Exception;

class UploadService
{
    public function salvarImagem(UploadedFile $arquivo, string $pasta, int $largura = 800, int $altura = null): string
    {
        $nome      = Str::uuid() . '.webp';
        $caminho   = $pasta . '/' . $nome;
        $imagem    = Image::read($arquivo);

        if ($altura) {
            $imagem->cover($largura, $altura);
        } else {
            $imagem->scaleDown(width: $largura);
        }

        Storage::disk('public')->put($caminho, $imagem->toWebp(85));

        return $caminho;
    }

    public function salvarImagemProduto(UploadedFile $arquivo): string
    {
        return $this->salvarImagem($arquivo, config('lanchonete.upload.imagens_produto'), 800, 800);
    }

    public function salvarImagemLoja(UploadedFile $arquivo, string $tipo = 'logo'): string
    {
        if ($tipo === 'banner') {
            return $this->salvarImagem($arquivo, config('lanchonete.upload.imagens_loja'), 1200, 400);
        }
        return $this->salvarImagem($arquivo, config('lanchonete.upload.imagens_loja'), 400, 400);
    }

    public function salvarFotoPerfil(UploadedFile $arquivo): string
    {
        return $this->salvarImagem($arquivo, config('lanchonete.upload.imagens_perfil'), 300, 300);
    }

    public function salvarBanner(UploadedFile $arquivo): string
    {
        return $this->salvarImagem($arquivo, config('lanchonete.upload.imagens_banner'), 1200, 400);
    }

    public function deletar(?string $caminho): void
    {
        if ($caminho && Storage::disk('public')->exists($caminho)) {
            Storage::disk('public')->delete($caminho);
        }
    }

    public function validarImagem(UploadedFile $arquivo): bool
    {
        $extensoesPermitidas = config('lanchonete.upload.formatos');
        $extensao = strtolower($arquivo->getClientOriginalExtension());
        $tamanhoMaxMb = config('lanchonete.upload.max_size_mb', 20);

        if (!in_array($extensao, $extensoesPermitidas)) return false;
        if ($arquivo->getSize() > ($tamanhoMaxMb * 1024 * 1024)) return false;

        return true;
    }
}
