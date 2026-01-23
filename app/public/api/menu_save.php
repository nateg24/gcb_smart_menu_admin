<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/config.php';
use BrewMenu\DB;
use BrewMenu\Auth;
use BrewMenu\WSNotify;

header('Content-Type: application/json');
Auth::requireAdmin();

$data = json_decode(file_get_contents('php://input') ?: '[]', true);
if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
  http_response_code(400);
  echo json_encode(['error'=>'items[] required']);
  exit;
}

$pdo = DB::pdo();
$menuId = 1;

$pdo->beginTransaction();
try {
  $stmt = $pdo->prepare("
    UPDATE menu_items
    SET name=?, style=?, abv=?, price=?, is_available=?, sort_order=?
    WHERE id=? AND menu_id=?
  ");

  foreach ($data['items'] as $it) {
    if (!isset($it['id'])) continue;
    $stmt->execute([
      (string)($it['name'] ?? ''),
      ($it['style'] ?? '') !== '' ? (string)$it['style'] : null,
      ($it['abv'] ?? '') === '' || $it['abv'] === null ? null : (float)$it['abv'],
      ($it['price'] ?? '') === '' || $it['price'] === null ? null : (float)$it['price'],
      !empty($it['is_available']) ? 1 : 0,
      (int)($it['sort_order'] ?? 0),
      (int)$it['id'],
      $menuId
    ]);
  }

  $pdo->prepare("UPDATE menus SET version = version + 1, updated_at = NOW() WHERE id = ?")->execute([$menuId]);
  $ver = $pdo->query("SELECT version FROM menus WHERE id = 1")->fetch();
  $newVersion = (int)($ver['version'] ?? 1);

  $pdo->commit();
  WSNotify::broadcastMenuUpdated($menuId, $newVersion);

  echo json_encode(['ok'=>true,'menu_id'=>$menuId,'version'=>$newVersion], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['error'=>'Save failed']);
}
