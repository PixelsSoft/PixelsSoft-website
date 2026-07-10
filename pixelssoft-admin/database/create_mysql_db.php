<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_DATABASE') ?: 'pixelssoft_cms';

$pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database '{$database}' is ready.\n";
