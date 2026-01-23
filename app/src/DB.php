<?php
declare(strict_types=1);

namespace BrewMenu;

require_once __DIR__ . '/config.php';
use PDO;

final class DB {
  public static function pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', env('DB_HOST','db'), env('DB_NAME','brew_menu'));
    $pdo = new PDO($dsn, env('DB_USER','brew'), env('DB_PASS','brewpass'), [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
  }
}
