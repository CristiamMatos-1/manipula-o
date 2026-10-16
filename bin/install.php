<?php
// Instalação via linha de comando (alternativa ao instalador web /install).
// php bin/install.php --host=localhost --port=3306 --db=drogacare --user=root --pass=segredo \
//     --url=https://www.seudominio.com.br --email=admin@drogacare.com.br [--smtp=smtps://...]
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$o = getopt('', ['host::', 'port::', 'db:', 'user:', 'pass::', 'url:', 'email::', 'smtp::', 'from::']);
if (!isset($o['db'], $o['user'], $o['url'])) {
    fwrite(STDERR, "Uso: php bin/install.php --db=NOME --user=USUARIO [--pass=SENHA] [--host=localhost] [--port=3306] --url=https://site [--email=admin@drogacare.com.br] [--smtp=URL]\n");
    exit(1);
}
try {
    $log = App\Core\Installer::run([
        'db_host' => $o['host'] ?? 'localhost',
        'db_port' => (int) ($o['port'] ?? 3306),
        'db_name' => $o['db'],
        'db_user' => $o['user'],
        'db_pass' => $o['pass'] ?? '',
        'app_url' => $o['url'],
        'admin_email' => $o['email'] ?? 'admin@drogacare.com.br',
        'smtp_url' => $o['smtp'] ?? '',
        'mail_from' => $o['from'] ?? 'Drogacare <nao-responda@drogacare.com.br>',
    ]);
    echo implode("\n", $log) . "\nInstalação concluída.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
