<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, User-Id");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request, just send the headers and stop execution
    http_response_code(204);
    exit;
}

include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = str_replace('/api.php', '', $request_uri);
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if ($path === '/me') {
            handleGetMe($pdo);
        } else {
            echo json_encode(['message' => 'Invalid request path']);
        }
        break;
        case 'POST':
            if ($path === '/register') {
            handleRegister($pdo, $input);
        } elseif ($path === '/login') {
            handleLogin($pdo, $input);
        } else {
            echo json_encode(['message' => 'Invalid request path']);
        }
        break;
    case 'PUT':
        if ($path === '/update') {
            handleUpdate($pdo, $input);
        } else {
            echo json_encode(['message' => 'Invalid request path']);
        }
        break;
    default:
        echo json_encode(['message' => 'Invalid request method']);
        break;
}

function handleRegister($pdo, $input) {
    try {
        if (!isset($input['fullname']) || !isset($input['email']) || !isset($input['password'])) {
            throw new Exception("Campos obrigatórios não fornecidos");
        }

        $sql = "INSERT INTO users (fullname, email, password) VALUES (:fullname, :email, :password)";
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            'fullname' => $input['fullname'],
            'email' => $input['email'],
            'password' => password_hash($input['password'], PASSWORD_BCRYPT)
        ]);

        if (!$result) {
            throw new Exception("Falha ao executar a query");
        }

        echo json_encode(['message' => 'User registered successfully']);
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
    }
}

function handleLogin($pdo, $input) {
    try {
        // Verificar se todos os campos necessários estão presentes
        if (!isset($input['email']) || !isset($input['password'])) {
            throw new Exception("Email e senha são obrigatórios");
        }

        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        
        if (!$stmt->execute(['email' => $input['email']])) {
            throw new Exception("Falha ao executar a query");
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($input['password'], $user['password'])) {
            // Remove a senha do objeto de usuário antes de enviar
            unset($user['password']);
            echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => $user, 'userId' => $user['id']]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch (PDOException $e) {
        // Captura erros específicos do PDO
        error_log("PDO Error in Login: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        // Captura outros erros
        error_log("Error in Login: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
    }
}

function handleGetMe($pdo) {
    try {
        $headers = getallheaders();
        $userId = isset($headers['User-Id']) ? $headers['User-Id'] : null;

        if (!$userId) {
            throw new Exception('User ID not provided', 401);
        }

        $sql = "SELECT fullname, email, phone, address, company, cpf FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        if (!$stmt->execute(['id' => $userId])) {
            throw new Exception('Failed to execute query');
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        echo json_encode(['success' => true, 'user' => $user]);
    } catch (PDOException $e) {
        // Captura erros específicos do PDO
        error_log("PDO Error in GetMe: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        // Captura outros erros
        error_log("Error in GetMe: " . $e->getMessage());
        http_response_code($e->getCode() ?: 500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function handleUpdate($pdo, $input) {
    $headers = getallheaders();
    $userId = isset($headers['User-Id']) ? $headers['User-Id'] : null;

    $fields = [];
    $values = [];

    if (isset($input['fullname'])) {
        $fields[] = 'fullname = :fullname';
        $values['fullname'] = $input['fullname'];
    }
    if (isset($input['email'])) {
        $fields[] = 'email = :email';
        $values['email'] = $input['email'];
    }
    if (isset($input['phone'])) {
        $fields[] = 'phone = :phone';
        $values['phone'] = $input['phone'];
    }
    if (isset($input['address'])) {
        $fields[] = 'address = :address';
        $values['address'] = $input['address'];
    }
    if (isset($input['company'])) {
        $fields[] = 'company = :company';
        $values['company'] = $input['company'];
    }
    if (isset($input['cpf'])) {
        $fields[] = 'cpf = :cpf';
        $values['cpf'] = $input['cpf'];
    }

    if (!empty($fields)) {
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $values['id'] = $userId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        echo json_encode(['message' => 'User profile updated successfully']);
    } else {
        echo json_encode(['message' => 'No fields to update']);
    }
}
?>