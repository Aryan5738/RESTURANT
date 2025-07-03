<?php
require_once __DIR__ . '/../config/functions.php';
session_destroy();
json_output(['success' => true]);
?>