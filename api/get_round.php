<?php
require_once __DIR__ . '/../config/functions.php';

$stmt  = $pdo->query('SELECT * FROM rounds ORDER BY id DESC LIMIT 1');
$round = $stmt->fetch();

if (!$round) {
    json_output(['error' => 'No round found'], 404);
}

json_output($round);
?>