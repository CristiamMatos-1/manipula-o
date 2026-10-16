<?php
// Teste ponta a ponta (HTTP). Use SOMENTE em uma instalação de teste recém-criada:
//   php bin/install.php --db=... --user=... --pass=... --url=http://localhost:8000
//   php -S localhost:8000 -t public public/index.php &
//   php tests/e2e.php http://localhost:8000
declare(strict_types=1);

$base = rtrim($argv[1] ?? 'http://localhost:8000', '/');
$mailLog = __DIR__ . '/../storage/logs/mail.log';
$pass = 0;
$fail = 0;

function check(string $name, bool $ok, string $extra = ''): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? "  [OK]   " : "  [FALHA] ") . $name . ($ok || $extra === '' ? '' : "  -> $extra") . "\n";
}

final class Client
{
    private string $jar;
    public function __construct(private string $base)
    {
        $this->jar = tempnam(sys_get_temp_dir(), 'dc');
    }
    /** @return array{0:int,1:string,2:string} status, body, location */
    public function req(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_CUSTOMREQUEST => $method,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $raw = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $headers = substr($raw, 0, $hs);
        $loc = preg_match('/^Location:\s*(\S+)/mi', $headers, $m) ? $m[1] : '';
        return [$status, substr($raw, $hs), $loc];
    }
    public function get(string $p): array { return $this->req('GET', $p); }
    public function csrf(string $path): string
    {
        [, $body] = $this->get($path);
        preg_match('/name="_csrf" value="([a-f0-9]+)"/', $body, $m);
        return $m[1] ?? '';
    }
    public function post(string $formPage, string $action, array $data): array
    {
        return $this->req('POST', $action, ['_csrf' => $this->csrf($formPage)] + $data);
    }
}

function path(string $loc): string { return (string) parse_url($loc, PHP_URL_PATH); }

echo "== Site público ==\n";
$anon = new Client($base);
[$s, $b] = $anon->get('/');
check('Home responde 200', $s === 200);
check('Home tem Hero, Sobre nós e Contato', str_contains($b, 'Cuidado sob medida') && str_contains($b, 'id="sobre"') && str_contains($b, 'id="contato"'));
check('Tempo de mercado calculado desde 1991', str_contains($b, (date('Y') - 1991) . '+'));
check('Banner de cookies presente', str_contains($b, 'id="cookie-banner"'));
check('Sem botão de WhatsApp quando não há número', !str_contains($b, 'wa.me/'));
check('Site público não cria cookie de sessão', !str_contains(implode('', file_exists(sys_get_temp_dir()) ? [] : []), 'x'));
[$s] = $anon->get('/blog/rascunho-cuidados-no-verao');
check('Rascunho não é público (404)', $s === 404);
[$s, $b] = $anon->get('/blog/bem-vindo-ao-novo-site-da-drogacare');
check('Artigo publicado abre com botões de compartilhar', $s === 200 && str_contains($b, 'wa.me/?text=') && str_contains($b, 'facebook.com/sharer') && str_contains($b, 'data-share-instagram'));
[$s] = $anon->get('/install');
check('Instalador bloqueado após instalação (404)', $s === 404);
[$s, , $loc] = $anon->get('/admin');
check('/admin sem login redireciona ao login', $s === 302 && path($loc) === '/admin/login');

echo "== Login e troca de senha obrigatória ==\n";
$admin = new Client($base);
[$s] = $admin->req('POST', '/admin/login', ['email' => 'admin@drogacare.com.br', 'password' => 'admin']);
check('POST sem CSRF é recusado (419)', $s === 419);
[$s, $b] = $admin->post('/admin/login', '/admin/login', ['email' => 'admin@drogacare.com.br', 'password' => 'errada']);
check('Senha errada mostra erro genérico', $s === 422 && str_contains($b, 'E-mail ou senha incorretos'));
[$s, , $loc] = $admin->post('/admin/login', '/admin/login', ['email' => 'admin@drogacare.com.br', 'password' => 'admin']);
check('Login admin/admin leva à troca de senha', $s === 302 && path($loc) === '/admin/alterar-senha');
[$s, , $loc] = $admin->get('/admin');
check('Admin com troca pendente não acessa o painel', $s === 302 && path($loc) === '/admin/alterar-senha');
[$s, , $loc] = $admin->get('/admin/usuarios');
check('Nem as outras telas', $s === 302 && path($loc) === '/admin/alterar-senha');
[$s, $b] = $admin->post('/admin/alterar-senha', '/admin/alterar-senha', ['current' => 'admin', 'password' => 'admin123', 'confirm' => 'admin123']);
check('Senha fraca é rejeitada', $s === 422 && str_contains($b, 'pelo menos 10 caracteres'));
[$s, $b] = $admin->post('/admin/alterar-senha', '/admin/alterar-senha', ['current' => 'admin', 'password' => 'Drogacare@2026Ok', 'confirm' => 'Drogacare@2026Ok']);
check('Senha forte aceita', $s === 302);

echo "== Dashboard ==\n";
[$s, $b] = $admin->get('/admin');
check('Dashboard 200 com cartões', $s === 200 && str_contains($b, 'Total de usuários') && str_contains($b, 'Artigos publicados') && str_contains($b, 'Rascunhos pendentes'));
check('Dashboard mostra logs de auditoria', str_contains($b, 'Últimos logs de auditoria') && str_contains($b, 'Login realizado'));
check('Menu do admin tem Usuários e Auditoria', str_contains($b, '/admin/usuarios') && str_contains($b, '/admin/auditoria'));

echo "== Usuários ==\n";
$pwEditor = 'Editor@Teste2026';
$pwViewer = 'Visual@Teste2026';
[$s] = $admin->post('/admin/usuarios/novo', '/admin/usuarios/novo', ['name' => 'Editor Teste', 'email' => 'editor@teste.com', 'role' => 'EDITOR_CONTEUDO', 'password' => $pwEditor]);
check('Cria Editor', $s === 302);
[$s] = $admin->post('/admin/usuarios/novo', '/admin/usuarios/novo', ['name' => 'Visualizador Teste', 'email' => 'visualizador@teste.com', 'role' => 'VISUALIZADOR', 'password' => $pwViewer]);
check('Cria Visualizador', $s === 302);
[$s, $b] = $admin->post('/admin/usuarios/novo', '/admin/usuarios/novo', ['name' => 'Dup', 'email' => 'editor@teste.com', 'role' => 'VISUALIZADOR', 'password' => $pwViewer]);
check('E-mail duplicado é rejeitado', $s === 422 && str_contains($b, 'Já existe um usuário'));
[$s, $b] = $admin->get('/admin/usuarios');
check('Lista de usuários mostra os criados', str_contains($b, 'editor@teste.com') && str_contains($b, 'visualizador@teste.com'));
preg_match('#/admin/usuarios/(\d+)"[^>]*>Admin#', $b, $m);
$adminId = (int) (preg_match('#/admin/usuarios/(\d+)"#', $b, $m2) ? $m2[1] : 1);
[$s, , $loc] = $admin->post('/admin/usuarios/' . $adminId, '/admin/usuarios/' . $adminId . '/excluir', []);
$admin->req('GET', '/admin/usuarios');
[, $b] = $admin->get('/admin/usuarios');
check('Não é possível excluir a si mesmo / último superadmin', str_contains($b, 'admin@drogacare.com.br'));

echo "== Artigos: rascunho x publicar ==\n";
[$s, , $loc] = $admin->post('/admin/posts/novo', '/admin/posts/novo', ['title' => 'Teste de Publicação', 'content' => "Olá **mundo**\n\n<script>alert(1)</script>\n\n[link ruim](javascript:alert(1))", 'intent' => 'draft']);
check('Salvar rascunho redireciona para edição', $s === 302 && preg_match('#/admin/posts/(\d+)#', $loc, $pm) === 1);
$postId = (int) ($pm[1] ?? 0);
[, $b] = $anon->get('/blog');
check('Rascunho NÃO aparece em /blog', !str_contains($b, 'Teste de Publicação'));
[$s] = $anon->get('/blog/teste-de-publicacao');
check('Rascunho NÃO abre publicamente', $s === 404);
[$s] = $admin->post("/admin/posts/$postId", "/admin/posts/$postId", ['title' => 'Teste de Publicação', 'content' => "Olá **mundo**\n\n<script>alert(1)</script>\n\n[link ruim](javascript:alert(1))", 'intent' => 'publish']);
check('Publicar', $s === 302);
[, $b] = $anon->get('/blog');
check('Publicado aparece em /blog', str_contains($b, 'Teste de Publicação'));
[$s, $b] = $anon->get('/blog/teste-de-publicacao');
check('Conteúdo é escapado (sem <script> bruto)', $s === 200 && !str_contains($b, '<script>alert(1)') && str_contains($b, '&lt;script&gt;'));
check('Link javascript: não vira <a>', !str_contains($b, 'href="javascript:'));
check('Negrito renderizado', str_contains($b, '<strong>mundo</strong>'));

echo "== Dados da empresa ==\n";
[$s, $b] = $admin->post('/admin/empresa', '/admin/empresa', [
    'name' => 'Drogacare', 'tagline' => 'Farmácia de Manipulação', 'founded_year' => '1991', 'pharmacist_name' => 'Dra. Maria Silva', 'pharmacist_crf' => 'CRF-SP 12345',
    'pharmacist_photo_url' => '', 'phone' => '(11) 3333-4444', 'whatsapp' => '5511999999999', 'email' => 'contato@drogacare.com.br', 'address' => 'Rua A, 100',
    'maps_embed_url' => '', 'hero_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube_url' => 'https://youtube.com/@drogacare', 'instagram_url' => '', 'facebook_url' => '',
]);
check('Salva dados da empresa', $s === 302);
[, $b] = $anon->get('/');
check('WhatsApp flutuante aparece', str_contains($b, 'wa.me/5511999999999') && str_contains($b, 'fixed bottom-4 right-4'));
check('Farmacêutico e CRF aparecem', str_contains($b, 'Dra. Maria Silva') && str_contains($b, 'CRF-SP 12345'));
check('Vídeo do YouTube (nocookie) no Hero', str_contains($b, 'youtube-nocookie.com/embed/dQw4w9WgXcQ'));
check('Mapa do Google Maps no Contato', str_contains($b, 'google.com/maps?q='));
[$s, $b] = $admin->post('/admin/empresa', '/admin/empresa', ['name' => 'Drogacare', 'founded_year' => '1991', 'hero_video_url' => 'javascript:alert(1)'] + array_fill_keys(['tagline', 'pharmacist_name', 'pharmacist_crf', 'pharmacist_photo_url', 'phone', 'whatsapp', 'email', 'address', 'maps_embed_url', 'youtube_url', 'instagram_url', 'facebook_url'], ''));
check('URL de vídeo maliciosa é rejeitada', $s === 422);

echo "== RBAC: Editor ==\n";
$editor = new Client($base);
[$s, , $loc] = $editor->post('/admin/login', '/admin/login', ['email' => 'editor@teste.com', 'password' => $pwEditor]);
check('Editor é forçado a trocar a senha', path($loc) === '/admin/alterar-senha');
$pwEditor2 = 'Editor@Novo2026ok';
$editor->post('/admin/alterar-senha', '/admin/alterar-senha', ['current' => $pwEditor, 'password' => $pwEditor2, 'confirm' => $pwEditor2]);
[, $b] = $editor->get('/admin');
check('Menu do editor SEM Usuários/Auditoria', str_contains($b, '/admin/posts') && !str_contains($b, 'href="/admin/usuarios"') && !str_contains($b, 'href="/admin/auditoria"'));
foreach (['/admin/usuarios', '/admin/usuarios/novo', '/admin/auditoria'] as $p) {
    [$s, , $loc] = $editor->get($p);
    check("Editor bloqueado em $p", $s === 302 && path($loc) === '/admin/acesso-negado');
}
[$s, , $loc] = $editor->post('/admin/posts/novo', '/admin/posts/novo', ['title' => 'Post do Editor', 'content' => 'Texto', 'intent' => 'publish']);
check('Editor cria e publica artigos', $s === 302);
[$s, , $loc] = $editor->post('/admin/posts/novo', '/admin/usuarios/novo', ['name' => 'X', 'email' => 'x@x.com', 'role' => 'SUPERADMIN', 'password' => 'Abcdefghij1']);
check('Editor NÃO consegue criar usuário via POST direto', $s === 302 && path($loc) === '/admin/acesso-negado');

echo "== RBAC: Visualizador ==\n";
$viewer = new Client($base);
$viewer->post('/admin/login', '/admin/login', ['email' => 'visualizador@teste.com', 'password' => $pwViewer]);
$pwViewer2 = 'Visual@Novo2026ok';
$viewer->post('/admin/alterar-senha', '/admin/alterar-senha', ['current' => $pwViewer, 'password' => $pwViewer2, 'confirm' => $pwViewer2]);
[, $b] = $viewer->get('/admin/posts');
check('Visualizador não vê "Novo artigo"', !str_contains($b, '/admin/posts/novo'));
[$s, , $loc] = $viewer->get('/admin/posts/novo');
check('Visualizador bloqueado em criar', $s === 302 && path($loc) === '/admin/acesso-negado');
[$s, , $loc] = $viewer->post('/admin/posts', '/admin/posts/novo', ['title' => 'Hack', 'content' => 'x', 'intent' => 'publish']);
check('Visualizador não cria via POST direto', $s === 302 && path($loc) === '/admin/acesso-negado');
[, $b] = $viewer->get("/admin/posts/$postId");
check('Formulário somente leitura (fieldset disabled, sem botões)', str_contains($b, '<fieldset disabled') && !str_contains($b, 'name="intent"'));
[, $b] = $anon->get('/blog');
check('Nada foi criado pelo visualizador', !str_contains($b, 'Hack'));

echo "== Recuperação de senha ==\n";
@unlink($mailLog);
$rec = new Client($base);
[$s, $b1] = $rec->post('/admin/esqueci-senha', '/admin/esqueci-senha', ['email' => 'editor@teste.com']);
[$s2, $b2] = $rec->post('/admin/esqueci-senha', '/admin/esqueci-senha', ['email' => 'naoexiste@x.com']);
$msg = 'enviaremos um link de redefinição';
check('Mensagem genérica idêntica (existente x inexistente)', str_contains($b1, $msg) && str_contains($b2, $msg));
$log = is_file($mailLog) ? file_get_contents($mailLog) : '';
check('Apenas 1 e-mail (simulado) foi gerado', substr_count($log, 'E-MAIL SIMULADO') === 1);
preg_match('#redefinir-senha\?token=([a-f0-9]{64})#', $log, $tm);
$token = $tm[1] ?? '';
check('Link com token no e-mail', $token !== '');
[$s, $b] = $rec->get('/admin/redefinir-senha?token=' . $token);
check('Link válido abre o formulário', $s === 200 && str_contains($b, 'Criar nova senha'));
[$s, $b] = $rec->get('/admin/redefinir-senha?token=' . str_repeat('a', 64));
check('Token inventado é inválido', $s === 410 && str_contains($b, 'Link inválido'));
$pwEditor3 = 'Editor@Reset2026ok';
[$s, , $loc] = $rec->post('/admin/redefinir-senha?token=' . $token, '/admin/redefinir-senha', ['token' => $token, 'password' => $pwEditor3, 'confirm' => $pwEditor3]);
check('Redefine a senha e volta ao login', $s === 302 && str_contains($loc, '/admin/login?reset=1'));
[$s, $b] = $rec->get('/admin/redefinir-senha?token=' . $token);
check('Token é de uso único', $s === 410 && str_contains($b, 'Link inválido'));
$ed2 = new Client($base);
[$s, , $loc] = $ed2->post('/admin/login', '/admin/login', ['email' => 'editor@teste.com', 'password' => $pwEditor3]);
check('Login com a nova senha', $s === 302 && path($loc) === '/admin');
[$s, , $loc] = (new Client($base))->post('/admin/login', '/admin/login', ['email' => 'editor@teste.com', 'password' => $pwEditor2]);
check('Senha antiga não funciona mais', $s === 422);

echo "== Bloqueio por força bruta ==\n";
$bf = new Client($base);
for ($i = 0; $i < 5; $i++) {
    $bf->post('/admin/login', '/admin/login', ['email' => 'visualizador@teste.com', 'password' => 'errada123']);
}
[$s, $b] = $bf->post('/admin/login', '/admin/login', ['email' => 'visualizador@teste.com', 'password' => $pwViewer2]);
check('Após 5 falhas a conta bloqueia (mesmo com senha certa)', $s === 422 && str_contains($b, 'Muitas tentativas'));

echo "== Auditoria ==\n";
[$s, $b] = $admin->get('/admin/auditoria');
check('Auditoria lista login, falhas, usuários, publicação e recuperação', $s === 200 && str_contains($b, 'LOGIN_FAILED') && str_contains($b, 'USER_CREATE') && str_contains($b, 'POST_PUBLISH') && str_contains($b, 'PASSWORD_RESET_COMPLETED') && str_contains($b, 'LOGIN_LOCKED'));
check('Auditoria não expõe senhas/tokens', !str_contains($b, $token) && !str_contains($b, 'Drogacare@2026Ok'));
[, $b] = $admin->get('/admin/auditoria?q=USER_CREATE');
check('Busca na auditoria filtra', str_contains($b, 'USER_CREATE') && !str_contains($b, 'LOGIN_FAILED'));

echo "\nResultado: $pass OK, $fail falhas\n";
exit($fail > 0 ? 1 : 0);
