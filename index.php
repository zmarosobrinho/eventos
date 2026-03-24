<?php
include('seguranca.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Frases - Início</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Sistema Interno de Frases</h1>
    <p class="subtitle">Escolha uma área do sistema:</p>

    <div class="menu-grid">
        <a class="card-link" href="produtos.php">Produtos</a>
        <a class="card-link" href="categorias.php">Categorias</a>
        <a class="card-link" href="mensagens.php">Mensagens</a>
        <a class="card-link" href="produto_categorias.php">Produto x Categorias</a>
        <a class="card-link" href="mensagem_categorias.php">Mensagem x Categorias</a>
        <a class="card-link destaque" href="live.php">Modo Live</a>
    </div>
</div>
</body>
</html>
