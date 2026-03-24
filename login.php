<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';

if (!empty($_SESSION['usuario_logado'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($usuario === SISTEMA_USUARIO && $senha === SISTEMA_SENHA) {
        $_SESSION['usuario_logado'] = SISTEMA_USUARIO;
        header('Location: index.php');
        exit;
    }

    $erro = 'Usuário ou senha inválidos.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistema de Frases</title>
    <link rel="stylesheet" href="assets/estilo.css">
</head>
<body class="login-body">
<div class="login-card">
    <h1>Entrar no sistema</h1>

    <?php if ($erro !== ''): ?>
        <p class="erro"><?= esc($erro) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="usuario">Usuário</label>
        <input id="usuario" name="usuario" required>

        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" required>

        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
