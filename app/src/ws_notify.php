<?php
declare(strict_types=1);

namespace BrewMenu;

require_once __DIR__ . '/config.php';

final class WSNotify {
  public static function broadcastMenuUpdated(int $menuId, int $version): void {
    $host  = 'ws';
    $port  = 8090;
    $token = 'change-me';

    $payload = json_encode([
      'token'   => $token,
      'type'    => 'menu_updated',
      'menu_id' => $menuId,
      'version' => $version
    ], JSON_UNESCAPED_SLASHES);

    $fp = @fsockopen($host, $port, $errno, $errstr, 0.5);
    if (!$fp) return;
    fwrite($fp, $payload . "\n");
    fclose($fp);
  }
}
