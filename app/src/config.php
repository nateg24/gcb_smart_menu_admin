<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$root = realpath(__DIR__ . '/..');
if ($root && file_exists($root . '/.env')) {
  Dotenv::createImmutable($root)->safeLoad();
}

function env(string $key, ?string $default = null): string {
  $v = $_ENV[$key] ?? getenv($key);
  return ($v !== false && $v !== null && $v !== '') ? (string)$v : (string)($default ?? '');
}
