<?php
// Copie para config/config.php (ou use o instalador web em /install, que gera este arquivo).
return [
    // URL pública do site, com https:// e sem barra final (usada em e-mails, sitemap, SEO).
    'app_url' => 'https://www.seudominio.com.br',

    // true exibe erros detalhados. NUNCA use true em produção.
    'debug' => false,

    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'banco',
        'user' => 'usuario',
        'pass' => 'senha',
    ],

    // Servidor SMTP para a recuperação de senha. Exemplos:
    //   smtps://contato%40seudominio.com.br:SENHA@mail.seudominio.com.br:465   (SSL)
    //   smtp://contato%40seudominio.com.br:SENHA@mail.seudominio.com.br:587    (STARTTLS)
    // Vazio = envio SIMULADO (grava em storage/logs/mail.log).
    'smtp_url' => '',
    'mail_from' => 'Drogacare <nao-responda@drogacare.com.br>',
];
