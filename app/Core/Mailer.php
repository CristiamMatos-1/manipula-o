<?php
declare(strict_types=1);

namespace App\Core;

/** Envio de e-mail via SMTP (smtps://, smtp:// + STARTTLS). Sem SMTP_URL, simula gravando em storage/logs/mail.log. */
final class Mailer
{
    public static function send(string $to, string $subject, string $text, string $html): bool
    {
        $smtp = (string) Config::get('smtp_url', '');
        if ($smtp === '') {
            $line = sprintf("[%s] E-MAIL SIMULADO\nPara: %s\nAssunto: %s\n\n%s\n%s\n", date('c'), $to, $subject, $text, str_repeat('-', 60));
            @file_put_contents(ROOT . '/storage/logs/mail.log', $line . "\n", FILE_APPEND | LOCK_EX);
            return true;
        }
        try {
            self::smtp($smtp, $to, $subject, $text, $html);
            return true;
        } catch (\Throwable $e) {
            error_log('Falha no envio de e-mail: ' . $e->getMessage());
            return false;
        }
    }

    private static function smtp(string $dsn, string $to, string $subject, string $text, string $html): void
    {
        $u = parse_url($dsn);
        if (!$u || empty($u['host'])) {
            throw new \RuntimeException('smtp_url inválida');
        }
        $secure = ($u['scheme'] ?? '') === 'smtps';
        $port = (int) ($u['port'] ?? ($secure ? 465 : 587));
        $user = isset($u['user']) ? rawurldecode($u['user']) : '';
        $pass = isset($u['pass']) ? rawurldecode($u['pass']) : '';
        $from = (string) Config::get('mail_from', 'Drogacare <nao-responda@drogacare.com.br>');
        preg_match('/<([^>]+)>/', $from, $m);
        $fromAddr = $m[1] ?? $from;
        $ehloHost = parse_url(Config::appUrl(), PHP_URL_HOST) ?: 'localhost';

        $fp = @stream_socket_client(($secure ? 'ssl://' : 'tcp://') . $u['host'] . ':' . $port, $errno, $errstr, 15);
        if (!$fp) {
            throw new \RuntimeException("Conexão SMTP falhou: $errstr");
        }
        stream_set_timeout($fp, 15);

        $read = static function () use ($fp): string {
            $out = '';
            while (($line = fgets($fp, 515)) !== false) {
                $out .= $line;
                if (strlen($line) < 4 || $line[3] === ' ') break;
            }
            return $out;
        };
        $cmd = static function (string $c, array $ok) use ($fp, $read): string {
            fwrite($fp, $c . "\r\n");
            $r = $read();
            if (!in_array((int) substr($r, 0, 3), $ok, true)) {
                throw new \RuntimeException('SMTP: ' . trim($r));
            }
            return $r;
        };

        $greet = $read();
        if ((int) substr($greet, 0, 3) !== 220) {
            throw new \RuntimeException('SMTP: ' . trim($greet));
        }
        $caps = $cmd('EHLO ' . $ehloHost, [250]);
        if (!$secure && stripos($caps, 'STARTTLS') !== false) {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('Falha ao iniciar TLS');
            }
            $cmd('EHLO ' . $ehloHost, [250]);
        }
        if ($user !== '') {
            $cmd('AUTH LOGIN', [334]);
            $cmd(base64_encode($user), [334]);
            $cmd(base64_encode($pass), [235]);
        }
        $cmd('MAIL FROM:<' . $fromAddr . '>', [250]);
        $cmd('RCPT TO:<' . $to . '>', [250, 251]);
        $cmd('DATA', [354]);

        $boundary = 'b' . bin2hex(random_bytes(8));
        $headers = [
            'From: ' . $from,
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'Date: ' . date('r'),
            'Message-ID: <' . bin2hex(random_bytes(8)) . '@' . $ehloHost . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text))
            . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . "--$boundary--\r\n";
        fwrite($fp, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        $r = $read();
        if ((int) substr($r, 0, 3) !== 250) {
            throw new \RuntimeException('SMTP: ' . trim($r));
        }
        @fwrite($fp, "QUIT\r\n");
        fclose($fp);
    }

    public static function passwordReset(string $name, string $link, int $minutes): array
    {
        $text = "Olá, {$name}!\n\nRecebemos um pedido para redefinir a senha do painel Drogacare.\nAcesse o link abaixo (válido por {$minutes} minutos e de uso único):\n\n{$link}\n\nSe você não fez esse pedido, ignore este e-mail: sua senha continua a mesma.";
        $html = '<div style="font-family:Arial,sans-serif;color:#061e3b"><h2 style="color:#2775bb">Drogacare</h2><p>Olá, ' . e($name) . '!</p><p>Recebemos um pedido para redefinir a senha do painel. O link é válido por ' . $minutes . ' minutos e só pode ser usado uma vez.</p><p><a href="' . e($link) . '" style="background:#2775bb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block">Redefinir minha senha</a></p><p style="font-size:13px;color:#555">Se você não fez esse pedido, ignore este e-mail.</p></div>';
        return [$text, $html];
    }
}
