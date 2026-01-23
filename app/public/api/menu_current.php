<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/config.php';
use BrewMenu\DB;

header('Content-Type: application/json');

$pdo = DB::pdo();
$menuId = 1;

$m = $pdo->prepare("SELECT id, name, version, updated_at FROM menus WHERE id = ?");
$m->execute([$menuId]);
$menu = $m->fetch();

if (!$menu) { http_response_code(404); echo json_encode(['error'=>'Menu not found']); exit; }

$i = $pdo->prepare("
  SELECT id, name, style, abv, price, is_available, sort_order
  FROM menu_items
  WHERE menu_id = ?
  ORDER BY sort_order ASC, id ASC
");
$i->execute([$menuId]);

echo json_encode(['menu'=>$menu,'items'=>$i->fetchAll()], JSON_UNESCAPED_SLASHES);
