<?php
include('seguranca.php');
require_once 'conexao.php';

$mensagem = '';
$erro = '';

$produtoId = isset($_GET['produto_id']) ? (int)$_GET['produto_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = (int)($_POST['produto_id'] ?? 0);

    try {
        if ($produtoId <= 0) {
            throw new Exception('Selecione um produto válido.');
        }

        $categoriasSelecionadas = $_POST['categoria_ids'] ?? [];
        if (!is_array($categoriasSelecionadas)) {
            $categoriasSelecionadas = [];
        }

        $pdo->beginTransaction();

        $del = $pdo->prepare('DELETE FROM FRASE_produtos_categorias WHERE produto_id = :produto_id');
        $del->execute([':produto_id' => $produtoId]);

        if (!empty($categoriasSelecionadas)) {
            $ins = $pdo->prepare('INSERT INTO FRASE_produtos_categorias (produto_id, categoria_id) VALUES (:produto_id, :categoria_id)');
            foreach ($categoriasSelecionadas as $categoriaId) {
                $ins->execute([
                    ':produto_id' => $produtoId,
                    ':categoria_id' => (int)$categoriaId,
                ]);
            }
        }

        $pdo->commit();
        $mensagem = 'Vínculos atualizados com sucesso.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erro = $e->getMessage();
    }
}

$produtos = $pdo->query('SELECT id, nome FROM FRASE_produtos ORDER BY nome ASC')->fetchAll();
$categorias = $pdo->query('SELECT id, nome FROM FRASE_categorias ORDER BY nome ASC')->fetchAll();

$vinculadas = [];
if ($produtoId > 0) {
    $stmt = $pdo->prepare('SELECT categoria_id FROM FRASE_produtos_categorias WHERE produto_id = :produto_id');
    $stmt->execute([':produto_id' => $produtoId]);
    $vinculadas = array_map('intval', array_column($stmt->fetchAll(), 'categoria_id'));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção de Categorias por Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Manutenção de Categorias do Produto</h1>
        <a href="index.php" class="btn-link">← Voltar</a>
    </div>

    <?php if ($mensagem): ?><p class="msg ok"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="msg erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

    <div class="card">
        <form method="get" class="inline-form">
            <label>Selecione o produto para atualizar categorias</label>
            <select name="produto_id" required>
                <option value="">-- Escolha --</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= (int)$produto['id'] ?>" <?= $produtoId === (int)$produto['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($produto['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary" type="submit">Carregar</button>
        </form>
    </div>

    <?php if ($produtoId > 0): ?>
    <div class="card">
        <form method="post">
            <input type="hidden" name="produto_id" value="<?= $produtoId ?>">
            <h2>Categorias vinculadas (manutenção)</h2>

            <div class="checks-grid">
                <?php foreach ($categorias as $categoria): ?>
                    <label class="check-item">
                        <input
                            type="checkbox"
                            name="categoria_ids[]"
                            value="<?= (int)$categoria['id'] ?>"
                            <?= in_array((int)$categoria['id'], $vinculadas, true) ? 'checked' : '' ?>
                        >
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-primary">Salvar vínculos</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
