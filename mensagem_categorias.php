<?php
require_once 'conexao.php';

$mensagem = '';
$erro = '';

$mensagemId = isset($_GET['mensagem_id']) ? (int)$_GET['mensagem_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagemId = (int)($_POST['mensagem_id'] ?? 0);

    try {
        if ($mensagemId <= 0) {
            throw new Exception('Selecione uma mensagem válida.');
        }

        $categoriasSelecionadas = $_POST['categoria_ids'] ?? [];
        if (!is_array($categoriasSelecionadas)) {
            $categoriasSelecionadas = [];
        }

        $pdo->beginTransaction();

        $del = $pdo->prepare('DELETE FROM FRASE_mensagens_categorias WHERE mensagem_id = :mensagem_id');
        $del->execute([':mensagem_id' => $mensagemId]);

        if (!empty($categoriasSelecionadas)) {
            $ins = $pdo->prepare('INSERT INTO FRASE_mensagens_categorias (mensagem_id, categoria_id) VALUES (:mensagem_id, :categoria_id)');
            foreach ($categoriasSelecionadas as $categoriaId) {
                $ins->execute([
                    ':mensagem_id' => $mensagemId,
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

$mensagens = $pdo->query('SELECT id, texto FROM FRASE_mensagens ORDER BY id DESC')->fetchAll();
$categorias = $pdo->query('SELECT id, nome FROM FRASE_categorias ORDER BY nome ASC')->fetchAll();

$vinculadas = [];
if ($mensagemId > 0) {
    $stmt = $pdo->prepare('SELECT categoria_id FROM FRASE_mensagens_categorias WHERE mensagem_id = :mensagem_id');
    $stmt->execute([':mensagem_id' => $mensagemId]);
    $vinculadas = array_map('intval', array_column($stmt->fetchAll(), 'categoria_id'));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção de Categorias por Mensagem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Manutenção de Categorias da Mensagem</h1>
        <a href="index.php" class="btn-link">← Voltar</a>
    </div>

    <?php if ($mensagem): ?><p class="msg ok"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="msg erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

    <div class="card">
        <form method="get" class="inline-form">
            <label>Selecione a mensagem para atualizar categorias</label>
            <select name="mensagem_id" required>
                <option value="">-- Escolha --</option>
                <?php foreach ($mensagens as $item): ?>
                    <option value="<?= (int)$item['id'] ?>" <?= $mensagemId === (int)$item['id'] ? 'selected' : '' ?>>
                        #<?= (int)$item['id'] ?> - <?= htmlspecialchars(mb_strimwidth($item['texto'], 0, 70, '...')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary" type="submit">Carregar</button>
        </form>
    </div>

    <?php if ($mensagemId > 0): ?>
    <div class="card">
        <form method="post">
            <input type="hidden" name="mensagem_id" value="<?= $mensagemId ?>">
            <h2>Categorias vinculadas</h2>

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
