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
  SELECT id, name, style, abv, price, is_available, non_guest_tap, sort_order
  FROM menu_items_beer
  WHERE menu_id = ?
  ORDER BY non_guest_tap DESC, sort_order ASC, name ASC
");
$i->execute([$menuId]);

echo json_encode(['menu'=>$menu,'items'=>$i->fetchAll()], JSON_UNESCAPED_SLASHES);
