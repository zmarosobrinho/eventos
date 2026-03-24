<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$banco = 'frases_lives';
$usuario = 'seu_usuario_mysql';
$senha = 'sua_senha_mysql';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$banco};charset={$charset}";

try {
    $pdo = new PDO($dsn, $usuario, $senha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    exit('Erro ao conectar ao banco de dados. Confira o arquivo conexao.php');
}
