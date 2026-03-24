<?php
require_once 'conexao.php';

$mensagem = '';
$erro = '';
$editando = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        if ($acao === 'criar') {
            $texto = trim($_POST['texto'] ?? '');
            if ($texto === '') {
                throw new Exception('O texto da mensagem é obrigatório.');
            }

            $stmt = $pdo->prepare('INSERT INTO FRASE_mensagens (texto) VALUES (:texto)');
            $stmt->execute([':texto' => $texto]);
            $mensagem = 'Mensagem cadastrada com sucesso.';
        }

        if ($acao === 'atualizar') {
            $id = (int)($_POST['id'] ?? 0);
            $texto = trim($_POST['texto'] ?? '');

            if ($id <= 0 || $texto === '') {
                throw new Exception('Dados inválidos para atualização.');
            }

            $stmt = $pdo->prepare('UPDATE FRASE_mensagens SET texto = :texto WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
                ':texto' => $texto,
            ]);
            $mensagem = 'Mensagem atualizada com sucesso.';
        }

        if ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID inválido para exclusão.');
            }

            $stmt = $pdo->prepare('DELETE FROM FRASE_mensagens WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $mensagem = 'Mensagem excluída com sucesso.';
        }
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $stmt = $pdo->prepare('SELECT id, texto FROM FRASE_mensagens WHERE id = :id');
    $stmt->execute([':id' => $idEditar]);
    $editando = $stmt->fetch();
}

$mensagens = $pdo->query('SELECT id, texto FROM FRASE_mensagens ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Mensagens / Frases</h1>
        <a href="index.php" class="btn-link">← Voltar</a>
    </div>

    <?php if ($mensagem): ?><p class="msg ok"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="msg erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

    <div class="card">
        <h2><?= $editando ? 'Editar mensagem' : 'Cadastrar mensagem' ?></h2>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'atualizar' : 'criar' ?>">
            <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?= (int)$editando['id'] ?>">
            <?php endif; ?>

            <label>Texto da frase/mensagem</label>
            <textarea name="texto" rows="5" required><?= htmlspecialchars($editando['texto'] ?? '') ?></textarea>

            <button type="submit" class="btn-primary"><?= $editando ? 'Salvar alterações' : 'Cadastrar mensagem' ?></button>
            <?php if ($editando): ?>
                <a class="btn-link" href="mensagens.php">Cancelar edição</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Lista de mensagens</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Texto</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($mensagens as $item): ?>
                <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td class="descricao-col"><?= nl2br(htmlspecialchars($item['texto'])) ?></td>
                    <td class="acoes">
                        <a class="btn-small" href="mensagens.php?editar=<?= (int)$item['id'] ?>">Editar</a>
                        <form method="post" onsubmit="return confirm('Excluir esta mensagem?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                            <button type="submit" class="btn-small danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
