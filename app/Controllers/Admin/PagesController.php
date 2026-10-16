<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

final class PagesController extends ContentController
{
    protected string $kind = 'page';
    protected string $basePath = '/admin/paginas';
    protected string $publicPath = '/p';
    protected string $singular = 'página';
    protected string $plural = 'Páginas';
    protected string $entity = 'Page';
}
