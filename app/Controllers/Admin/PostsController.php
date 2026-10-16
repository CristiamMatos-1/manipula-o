<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

final class PostsController extends ContentController
{
    protected string $kind = 'post';
    protected string $basePath = '/admin/posts';
    protected string $publicPath = '/blog';
    protected string $singular = 'artigo';
    protected string $plural = 'Artigos';
    protected string $entity = 'Post';
}
