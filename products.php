<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO products (name, description) VALUES (:name, :description)');
        $stmt->execute([
            'name' => $name,
            'description' => $description,
        ]);
    }

    redirect('products.php');
}

$products = $pdo->query('SELECT id, name, description, created_at FROM products ORDER BY id DESC')->fetchAll();

require __DIR__ . '/header.php';
?>
<main>
    <section class="card">
        <h2>Cadastrar produto</h2>
        <form method="post">
            <label for="name">Nome do produto</label>
            <input id="name" name="name" required>

            <label for="description">Descrição (MEDIUMTEXT)</label>
            <textarea id="description" name="description" rows="5"></textarea>

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
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= (int) $product['id'] ?></td>
                    <td><?= h($product['name']) ?></td>
                    <td><?= nl2br(h($product['description'])) ?></td>
                    <td><?= h($product['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
