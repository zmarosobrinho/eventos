<?php

declare(strict_types=1);

require_once __DIR__ . '/conexao.php';

const SISTEMA_USUARIO = 'admin';
const SISTEMA_SENHA = 'troque_esta_senha_forte';

function esc(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function exigir_login(): void
{
    if (empty($_SESSION['usuario_logado'])) {
        header('Location: login.php');
        exit;
    }
}
