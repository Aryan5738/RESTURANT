<?php
require_once __DIR__ . '/../config/functions.php';
$stmt=$pdo->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 1');
$note=$stmt->fetch();
if($note){ json_output($note); } else { json_output(['success'=>false]); }
?>