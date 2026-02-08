<?php
// api/orders.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['error'=>'Login required']);
  exit;
}
$user = $_SESSION['user'];

if ($method === 'GET') {
  // return orders for user; admin can see all
  if ($user['role'] === 'admin') {
    $stmt = $pdo->query('SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC');
    $orders = $stmt->fetchAll();
  } else {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();
  }
  // attach items
  foreach ($orders as &$ord) {
    $stmt = $pdo->prepare('SELECT oi.*, f.name as food_name FROM order_items oi JOIN foods f ON oi.food_id=f.id WHERE oi.order_id=?');
    $stmt->execute([$ord['id']]);
    $ord['items'] = $stmt->fetchAll();
  }
  echo json_encode($orders);
  exit;
}

if ($method === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $items = $input['items'] ?? [];
  if (!is_array($items) || count($items) === 0) {
    http_response_code(400);
    json_response(['error'=>'No items']);
  }
  $total = 0;
  foreach ($items as $it) {
    $qty = (int)($it['quantity'] ?? 0);
    $price = (int)($it['price_each'] ?? 0);
    if ($qty <= 0 || $price <= 0) { http_response_code(400); json_response(['error'=>'Invalid item']); }
    $total += $qty * $price;
  }
  $pdo->beginTransaction();
  $stmt = $pdo->prepare('INSERT INTO orders (user_id,total_price) VALUES (?,?)');
  $stmt->execute([$user['id'],$total]);
  $order_id = $pdo->lastInsertId();
  $stmt = $pdo->prepare('INSERT INTO order_items (order_id,food_id,quantity,price_each) VALUES (?,?,?,?)');
  foreach ($items as $it) {
    $stmt->execute([$order_id,(int)$it['food_id'],(int)$it['quantity'],(int)$it['price_each']]);
  }
  $pdo->commit();
  json_response(['success'=>true,'order_id'=>$order_id]);
}

http_response_code(405);
json_response(['error'=>'Method not allowed']);
?>