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
            $descricao = trim($_POST['descricao'] ?? '');

            if ($nome === '') {
                throw new Exception('O nome do produto é obrigatório.');
            }

            $stmt = $pdo->prepare('INSERT INTO FRASE_produtos (nome, descricao) VALUES (:nome, :descricao)');
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
            ]);
            $mensagem = 'Produto cadastrado com sucesso.';
        }

        if ($acao === 'atualizar') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($id <= 0 || $nome === '') {
                throw new Exception('Dados inválidos para atualização.');
            }

            $stmt = $pdo->prepare('UPDATE FRASE_produtos SET nome = :nome, descricao = :descricao WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
                ':nome' => $nome,
                ':descricao' => $descricao,
            ]);
            $mensagem = 'Produto atualizado com sucesso.';
        }

        if ($acao === 'excluir') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID inválido para exclusão.');
            }

            $stmt = $pdo->prepare('DELETE FROM FRASE_produtos WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $mensagem = 'Produto excluído com sucesso.';
        }
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $stmt = $pdo->prepare('SELECT id, nome, descricao FROM FRASE_produtos WHERE id = :id');
    $stmt->execute([':id' => $idEditar]);
    $editando = $stmt->fetch();
}

$produtos = $pdo->query('SELECT id, nome, descricao FROM FRASE_produtos ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Produtos</h1>
        <a href="index.php" class="btn-link">← Voltar</a>
    </div>

    <?php if ($mensagem): ?><p class="msg ok"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="msg erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

    <div class="card">
        <h2><?= $editando ? 'Editar produto' : 'Cadastrar produto' ?></h2>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'atualizar' : 'criar' ?>">
            <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?= (int)$editando['id'] ?>">
            <?php endif; ?>

            <label>Nome</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($editando['nome'] ?? '') ?>">

            <label>Descrição</label>
            <textarea name="descricao" rows="5"><?= htmlspecialchars($editando['descricao'] ?? '') ?></textarea>

            <button type="submit" class="btn-primary"><?= $editando ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
            <?php if ($editando): ?>
                <a class="btn-link" href="produtos.php">Cancelar edição</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Lista de produtos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= (int)$produto['id'] ?></td>
                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                    <td class="descricao-col"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></td>
                    <td class="acoes">
                        <a class="btn-small" href="produtos.php?editar=<?= (int)$produto['id'] ?>">Editar</a>
                        <form method="post" onsubmit="return confirm('Excluir este produto?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= (int)$produto['id'] ?>">
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
