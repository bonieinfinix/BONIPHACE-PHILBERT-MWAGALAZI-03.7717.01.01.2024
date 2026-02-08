<?php
// api/foods.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $q = $_GET['q'] ?? '';
  $category = $_GET['category'] ?? '';
  $sql = 'SELECT f.*, c.name AS category_name FROM foods f JOIN categories c ON f.category_id=c.id WHERE 1';
  $params = [];
  if ($q) {
    $sql .= ' AND (f.name LIKE ? OR f.description LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%";
  }
  if ($category) {
    $sql .= ' AND c.id = ?';
    $params[] = (int)$category;
  }
  $sql .= ' ORDER BY f.created_at DESC';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $foods = $stmt->fetchAll();
  echo json_encode($foods);
  exit;
}

// POST: create food (admin)
if ($method === 'POST') {
  if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    json_response(['error'=>'Admin only']);
  }
  $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $name = trim($input['name'] ?? '');
  $description = trim($input['description'] ?? '');
  $price = (int)($input['price'] ?? 0);
  $category_id = (int)($input['category_id'] ?? 0);
  $image = $input['image'] ?? null; // can be URL or base64
  if (!$name || $price <= 0 || $category_id <= 0) {
    http_response_code(400);
    json_response(['error'=>'Invalid input']);
  }
  $stmt = $pdo->prepare('INSERT INTO foods (name,description,price,category_id,image) VALUES (?,?,?,?,?)');
  $stmt->execute([$name,$description,$price,$category_id,$image]);
  json_response(['success'=>true,'id'=>$pdo->lastInsertId()]);
}

// PUT/DELETE using method override via POST action param for simpler clients
if ($method === 'POST' && !empty($_POST['action'])) {
  $action = $_POST['action'];
  if ($action === 'update') {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { http_response_code(403); json_response(['error'=>'Admin only']); }
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (int)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $image = $_POST['image'] ?? null;
    $stmt = $pdo->prepare('UPDATE foods SET name=?,description=?,price=?,category_id=?,image=? WHERE id=?');
    $stmt->execute([$name,$description,$price,$category_id,$image,$id]);
    json_response(['success'=>true]);
  }
  if ($action === 'delete') {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { http_response_code(403); json_response(['error'=>'Admin only']); }
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM foods WHERE id=?');
    $stmt->execute([$id]);
    json_response(['success'=>true]);
  }
}

http_response_code(405);
json_response(['error'=>'Method not allowed']);
?>