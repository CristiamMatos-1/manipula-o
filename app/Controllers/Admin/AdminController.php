<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\{Request, Upload};

abstract class AdminController extends Controller
{
    /**
     * Resolve o valor final de um campo de imagem do formulário:
     * novo arquivo enviado (campo "{coluna}_file") > remoção marcada ("{coluna}_remove") > valor atual.
     * Em caso de erro no upload, registra em $errors[coluna] e mantém o valor atual.
     */
    protected function imageValue(string $column, ?string $current, array &$errors): ?string
    {
        [$path, $error] = Upload::image($column . '_file');
        if ($error !== null) {
            $errors[$column] = $error;
            return $current;
        }
        if ($path !== null) {
            return $path;
        }
        return Request::str($column . '_remove') === '1' ? null : $current;
    }

    /** Apaga do disco a imagem antiga quando ela foi trocada ou removida. */
    protected function discardReplacedImage(?string $old, ?string $new): void
    {
        if ($old !== null && $old !== '' && $old !== $new) {
            Upload::delete($old);
        }
    }
}
