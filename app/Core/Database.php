<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Acesso ao MySQL/MariaDB via PDO. Todas as consultas usam prepared statements. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $db, bool $emulatePrepares = false): PDO
    {
        $host = (string) ($db['host'] ?? 'localhost');
        try {
            return self::open($host, $db, $emulatePrepares);
        } catch (\PDOException $e) {
            // Alguns servidores não expõem o socket padrão: tenta TCP em 127.0.0.1.
            if ($host === 'localhost' && str_contains($e->getMessage(), '[2002]')) {
                return self::open('127.0.0.1', $db, $emulatePrepares);
            }
            throw $e;
        }
    }

    private static function open(string $host, array $db, bool $emulatePrepares): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, (int) ($db['port'] ?? 3306), $db['name'] ?? '');
        return new PDO($dsn, (string) ($db['user'] ?? ''), (string) ($db['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => $emulatePrepares,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '" . date('P') . "', sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'",
        ]);
    }

    public static function pdo(): PDO
    {
        return self::$pdo ??= self::connect((array) Config::get('db', []));
    }

    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function value(string $sql, array $params = []): mixed
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function run(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }
}
