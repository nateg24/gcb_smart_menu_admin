<?php
declare(strict_types=1);

namespace BrewMenu;

final class Auth {
  public static function requireAdmin(): void {
    $pin = $_SERVER['HTTP_X_ADMIN_PIN'] ?? '';
    if ($pin !== '1234') {
      http_response_code(401);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Unauthorized']);
      exit;
    }
  }
}
