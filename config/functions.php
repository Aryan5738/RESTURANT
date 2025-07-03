<?php
require_once __DIR__ . '/db.php';

session_start();

function json_output($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generate_uid(): string
{
    return '91CLUB' . random_int(10000, 99999);
}

function get_user_by_phone(string $phone)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = ? LIMIT 1');
    $stmt->execute([$phone]);
    return $stmt->fetch();
}

function require_post(array $fields): void
{
    foreach ($fields as $field) {
        if (!isset($_POST[$field])) {
            json_output(['error' => 'Missing field ' . $field], 400);
        }
    }
}
?>