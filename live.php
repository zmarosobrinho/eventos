<?php
include('seguranca.php');
session_start();
require_once 'conexao.php';

$produtoId = 0;
if (isset($_GET['product_id'])) {
    $produtoId = (int)$_GET['product_id'];
    $_SESSION['live_product_id'] = $produtoId;
} elseif (isset($_SESSION['live_product_id'])) {
    $produtoId = (int)$_SESSION['live_product_id'];
}

$produtos = $pdo->query('SELECT id, nome FROM FRASE_produtos ORDER BY nome ASC')->fetchAll();

$produtoSelecionado = null;
$frases = [];

if ($produtoId > 0) {
    $stmtProduto = $pdo->prepare('SELECT id, nome, descricao FROM FRASE_produtos WHERE id = :id');
    $stmtProduto->execute([':id' => $produtoId]);
    $produtoSelecionado = $stmtProduto->fetch();

    if ($produtoSelecionado) {
        // DISTINCT evita duplicação caso a mesma frase esteja em várias categorias do produto.
        $sql = '
            SELECT DISTINCT m.id, m.texto
            FROM FRASE_mensagens m
            INNER JOIN FRASE_mensagens_categorias mc ON mc.mensagem_id = m.id
            INNER JOIN FRASE_produtos_categorias pc ON pc.categoria_id = mc.categoria_id
            WHERE pc.produto_id = :produto_id
            ORDER BY RAND()
        ';
        $stmtFrases = $pdo->prepare($sql);
        $stmtFrases->execute([':produto_id' => $produtoId]);
        $frases = $stmtFrases->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live - Frases por Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container live-container">
    <div class="top-bar">
        <h1>Modo Live</h1>
        <a href="index.php" class="btn-link">← Voltar</a>
    </div>

    <div class="card">
        <form method="get" class="inline-form">
            <label>Produto</label>
            <select name="product_id" required>
                <option value="">-- Selecione --</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= (int)$produto['id'] ?>" <?= $produtoId === (int)$produto['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($produto['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary" type="submit">Mostrar</button>
            <?php if ($produtoId > 0): ?>
                <a class="btn-small" href="live.php?product_id=<?= $produtoId ?>">Sortear novamente</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($produtoSelecionado): ?>
        <div class="card produto-detalhe">
            <h2><?= htmlspecialchars($produtoSelecionado['nome']) ?></h2>
            <p><?= nl2br(htmlspecialchars($produtoSelecionado['descricao'])) ?></p>
        </div>

        <div class="live-grid">
            <?php if (empty($frases)): ?>
                <p class="msg">Nenhuma frase ligada às categorias deste produto.</p>
            <?php else: ?>
                <?php foreach ($frases as $frase): ?>
                    <div class="frase-card">
                        <?= nl2br(htmlspecialchars($frase['texto'])) ?>
                        <a
                            href="mensagens.php?editar=<?= (int)$frase['id'] ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="frase-edit-link"
                        > #</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
