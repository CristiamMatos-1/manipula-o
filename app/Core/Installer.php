<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Cria o schema, o admin inicial (admin/admin com troca obrigatória), dados de exemplo e o config.php. */
final class Installer
{
    /**
     * @param array{db_host:string,db_port:int,db_name:string,db_user:string,db_pass:string,app_url:string,admin_email:string,smtp_url?:string,mail_from?:string} $in
     * @return array<int,string> mensagens de progresso
     */
    public static function run(array $in): array
    {
        $log = [];
        $db = ['host' => $in['db_host'], 'port' => $in['db_port'], 'name' => $in['db_name'], 'user' => $in['db_user'], 'pass' => $in['db_pass']];
        $pdo = Database::connect($db);
        $log[] = 'Conexão com o banco de dados estabelecida.';

        // Conexão separada com prepares emulados para executar o arquivo com várias instruções.
        $multi = Database::connect($db, true);
        $multi->exec((string) file_get_contents(ROOT . '/database/schema.sql'));
        $log[] = 'Tabelas criadas/verificadas.';

        $email = strtolower($in['admin_email']);
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$email]);
        if (!$exists->fetch()) {
            $pdo->prepare("INSERT INTO users (name, email, password_hash, role, must_change_password, created_at, updated_at) VALUES ('Administrador', ?, ?, 'SUPERADMIN', 1, NOW(), NOW())")
                ->execute([$email, Password::hash('admin')]);
            $adminId = (int) $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO audit_logs (user_id, user_email, action, entity, entity_id, details, created_at) VALUES (?, ?, 'SEED_ADMIN', 'User', ?, 'Administrador inicial criado pela instalação (troca de senha obrigatória).', NOW())")
                ->execute([$adminId, $email, (string) $adminId]);
            $log[] = "Administrador criado: {$email} / admin (a senha deverá ser trocada no 1º login).";
        } else {
            $log[] = 'Administrador já existia; nada alterado.';
        }

        self::seedContent($pdo, $email);
        $log[] = 'Dados iniciais (empresa, política de privacidade, artigos de exemplo) verificados.';

        $config = [
            'app_url' => rtrim($in['app_url'], '/'),
            'debug' => false,
            'db' => $db,
            'smtp_url' => $in['smtp_url'] ?? '',
            'mail_from' => $in['mail_from'] ?? 'Drogacare <nao-responda@drogacare.com.br>',
        ];
        $php = "<?php\n// Gerado pelo instalador em " . date('c') . ". Contém segredos: NUNCA publique este arquivo.\nreturn " . var_export($config, true) . ";\n";
        if (@file_put_contents(ROOT . '/config/config.php', $php, LOCK_EX) === false) {
            throw new \RuntimeException('Não foi possível gravar config/config.php. Verifique a permissão de escrita da pasta config/.');
        }
        @chmod(ROOT . '/config/config.php', 0640);
        if (@file_put_contents(ROOT . '/storage/installed.lock', date('c')) === false) {
            throw new \RuntimeException('Não foi possível gravar storage/installed.lock. Verifique a permissão de escrita da pasta storage/.');
        }
        $log[] = 'Configuração gravada em config/config.php.';
        return $log;
    }

    private static function seedContent(PDO $pdo, string $adminEmail): void
    {
        $pdo->exec("INSERT IGNORE INTO company_info (id, name, tagline, founded_year, pharmacist_name, pharmacist_crf, pharmacist_photo_url, phone, email, address)
            VALUES (1, 'Drogacare', 'Farmácia de Manipulação', 1991, 'Nome do Farmacêutico Responsável', 'CRF-XX 00000', '/assets/img/avatar-placeholder.svg', '(00) 0000-0000', 'contato@drogacare.com.br', 'Endereço da farmácia - editar em Painel > Dados da empresa')");

        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$adminEmail]);
        $authorId = $st->fetchColumn() ?: null;

        $tpl = 'INSERT IGNORE INTO %s (title, slug, excerpt, content, status, published_at, author_id, created_at, updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())';
        $page = $pdo->prepare(sprintf($tpl, 'pages'));
        $post = $pdo->prepare(sprintf($tpl, 'posts'));
        $now = date('Y-m-d H:i:s');

        $page->execute([
            'Política de Privacidade', 'politica-de-privacidade',
            'Como a Drogacare trata dados pessoais conforme a LGPD (Lei 13.709/2018).',
            "## Quais dados coletamos\n\nEste site utiliza apenas cookies essenciais ao funcionamento e, mediante seu consentimento, cookies de medição. Dados enviados por WhatsApp ou e-mail são usados somente para atendê-lo.\n\n## Seus direitos\n\nVocê pode solicitar acesso, correção ou exclusão de seus dados a qualquer momento pelo e-mail de contato informado no rodapé.\n\n## Revise este texto\n\nEste é um texto-modelo. Peça à assessoria jurídica para revisá-lo antes da publicação definitiva.",
            'PUBLISHED', $now, $authorId,
        ]);
        $post->execute([
            'Bem-vindo ao novo site da Drogacare', 'bem-vindo-ao-novo-site-da-drogacare',
            'Conheça nosso novo canal e saiba como a manipulação personalizada cuida de você.',
            "Desde 1991 a Drogacare cuida da sua saúde com **medicamentos manipulados sob medida**.\n\n## O que é manipulação?\n\nÉ a preparação de fórmulas individualizadas, com dose e forma farmacêutica ajustadas à prescrição do seu médico.\n\n- Atendimento farmacêutico especializado\n- Qualidade e rastreabilidade em cada fórmula\n- Mais de três décadas de tradição",
            'PUBLISHED', $now, $authorId,
        ]);
        $post->execute([
            'Cuidados com a pele no verão (rascunho)', 'rascunho-cuidados-no-verao',
            'Rascunho de exemplo: visível apenas no painel até ser publicado.',
            'Escreva aqui o conteúdo do artigo.', 'DRAFT', null, $authorId,
        ]);
    }
}
