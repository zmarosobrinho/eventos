<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($nome !== '') {
        $stmt = $pdo->prepare('INSERT INTO produtos (nome, descricao) VALUES (:nome, :descricao)');
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
        ]);
    }

    header('Location: produtos.php');
    exit;
}

$produtos = $pdo->query('SELECT id, nome, descricao, criado_em FROM produtos ORDER BY id DESC')->fetchAll();

require __DIR__ . '/topo.php';
?>
<main>
    <section class="card">
        <h2>Cadastro de produtos</h2>
        <form method="post">
            <label for="nome">Nome do produto</label>
            <input id="nome" name="nome" required>

            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="5"></textarea>

            <button type="submit">Salvar produto</button>
        </form>
    </section>

    <section class="card">
        <h2>Produtos cadastrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= (int) $produto['id'] ?></td>
                        <td><?= esc($produto['nome']) ?></td>
                        <td><?= nl2br(esc((string) $produto['descricao'])) ?></td>
                        <td><?= esc($produto['criado_em']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
