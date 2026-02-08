<?php
// api/admin.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { http_response_code(403); echo json_encode(['error'=>'Admin only']); exit; }
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  // return users and orders overview
  $users = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC')->fetchAll();
  $orders = $pdo->query('SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC')->fetchAll();
  foreach ($orders as &$ord) {
    $stmt = $pdo->prepare('SELECT oi.*, f.name as food_name FROM order_items oi JOIN foods f ON oi.food_id=f.id WHERE oi.order_id=?');
    $stmt->execute([$ord['id']]);
    $ord['items'] = $stmt->fetchAll();
  }
  echo json_encode(['users'=>$users,'orders'=>$orders]);
  exit;
}

if ($method === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $action = $input['action'] ?? $input['action'] ?? '';
  if ($action === 'change_role') {
    $uid = (int)($input['user_id'] ?? 0);
    $role = ($input['role'] === 'admin') ? 'admin' : 'user';
    $stmt = $pdo->prepare('UPDATE users SET role=? WHERE id=?');
    $stmt->execute([$role,$uid]);
    echo json_encode(['success'=>true]); exit;
  }
  if ($action === 'update_order_status') {
    $oid = (int)($input['order_id'] ?? 0);
    $status = in_array($input['status'] ?? '', ['pending','processing','completed','cancelled']) ? $input['status'] : 'pending';
    $stmt = $pdo->prepare('UPDATE orders SET status=? WHERE id=?');
    $stmt->execute([$status,$oid]);
    echo json_encode(['success'=>true]); exit;
  }
}
http_response_code(400);
echo json_encode(['error'=>'Bad request']);
?>