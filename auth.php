<?php
// api/auth.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
  $action = $_GET['action'] ?? '';
  $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

  if ($action === 'register') {
    $name = trim($input['name'] ?? '');
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $input['password'] ?? '';
    if (!$name || !$email || strlen($password) < 6) {
      http_response_code(400);
      json_response(['error' => 'Invalid input. Name, valid email and password >=6 required.']);
    }
    // check exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      http_response_code(400);
      json_response(['error' => 'Email already registered.']);
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
    $stmt->execute([$name,$email,$hash]);
    json_response(['success' => true]);
  }

  if ($action === 'login') {
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $input['password'] ?? '';
    if (!$email || !$password) {
      http_response_code(400);
      json_response(['error' => 'Invalid credentials.']);
    }
    $stmt = $pdo->prepare('SELECT id,name,email,password,role FROM users WHERE email=?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
      http_response_code(401);
      json_response(['error' => 'Invalid credentials.']);
    }
    // store session
    $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
    json_response(['success'=>true,'user'=>$_SESSION['user']]);
  }
}

if ($method === 'GET') {
  // logout or current
  $action = $_GET['action'] ?? '';
  if ($action === 'me') {
    if (!empty($_SESSION['user'])) json_response(['user'=>$_SESSION['user']]);
    json_response(['user'=>null]);
  }
  if ($action === 'logout') {
    session_destroy();
    json_response(['success'=>true]);
  }
}

http_response_code(405);
json_response(['error'=>'Method not allowed']);
?>