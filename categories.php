<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
    }

    redirect('categories.php');
}

$categories = $pdo->query('SELECT id, name, created_at FROM categories ORDER BY id DESC')->fetchAll();

require __DIR__ . '/header.php';
?>
<main>
    <section class="card">
        <h2>Cadastrar categoria</h2>
        <form method="post">
            <label for="name">Nome da categoria</label>
            <input id="name" name="name" required>
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
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= (int) $category['id'] ?></td>
                    <td><?= h($category['name']) ?></td>
                    <td><?= h($category['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
