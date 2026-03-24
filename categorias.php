<?php
include('seguranca.php');
require_once 'conexao.php';

$mensagem = '';
$erro = '';
$editando = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            if ($nome === '') {
                throw new Exception('O nome da categoria é obrigatório.');
            }

            $stmt = $pdo->prepare('INSERT INTO FRASE_categorias (nome) VALUES (:nome)');
            $stmt->execute([':nome' => $nome]);
            $mensagem = 'Categoria cadastrada com sucesso.';
        }

        if ($acao === 'atualizar') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');

            if ($id <= 0 || $nome === '') {
                throw new Exception('Dados inválidos para atualização.');
            }

            $stmt = $pdo->prepare('UPDATE FRASE_categorias SET nome = :nome WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
                ':nome' => $nome,
            ]);
            $mensagem = 'Categoria atualizada com sucesso.';
        }

        if ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID inválido para exclusão.');
            }

            $stmt = $pdo->prepare('DELETE FROM FRASE_categorias WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $mensagem = 'Categoria excluída com sucesso.';
        }
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $stmt = $pdo->prepare('SELECT id, nome FROM FRASE_categorias WHERE id = :id');
    $stmt->execute([':id' => $idEditar]);
    $editando = $stmt->fetch();
}

$categorias = $pdo->query('SELECT id, nome FROM FRASE_categorias ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Categorias</h1>
        <a href="index.php" class="btn-link">← Voltar</a>
    </div>

    <?php if ($mensagem): ?><p class="msg ok"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="msg erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

    <div class="card">
        <h2><?= $editando ? 'Editar categoria' : 'Cadastrar categoria' ?></h2>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'atualizar' : 'criar' ?>">
            <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?= (int)$editando['id'] ?>">
            <?php endif; ?>

            <label>Nome</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($editando['nome'] ?? '') ?>">

            <button type="submit" class="btn-primary"><?= $editando ? 'Salvar alterações' : 'Cadastrar categoria' ?></button>
            <?php if ($editando): ?>
                <a class="btn-link" href="categorias.php">Cancelar edição</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Lista de categorias</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?= (int)$categoria['id'] ?></td>
                    <td><?= htmlspecialchars($categoria['nome']) ?></td>
                    <td class="acoes">
                        <a class="btn-small" href="categorias.php?editar=<?= (int)$categoria['id'] ?>">Editar</a>
                        <form method="post" onsubmit="return confirm('Excluir esta categoria?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
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
