<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    if ($nome !== '') {
        $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        $stmt->execute(['nome' => $nome]);
    }

    header('Location: categorias.php');
    exit;
}

$categorias = $pdo->query('SELECT id, nome, criado_em FROM categorias ORDER BY id DESC')->fetchAll();

require __DIR__ . '/topo.php';
?>
<main>
    <section class="card">
        <h2>Cadastro de categorias</h2>
        <form method="post">
            <label for="nome">Nome da categoria</label>
            <input id="nome" name="nome" required>
            <button type="submit">Salvar categoria</button>
        </form>
    </section>

    <section class="card">
        <h2>Categorias cadastradas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?= (int) $categoria['id'] ?></td>
                        <td><?= esc($categoria['nome']) ?></td>
                        <td><?= esc($categoria['criado_em']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
