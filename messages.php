<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);

    if ($title !== '' && $content !== '') {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO messages (title, content) VALUES (:title, :content)');
            $stmt->execute([
                'title' => $title,
                'content' => $content,
            ]);

            $messageId = (int) $pdo->lastInsertId();
            if ($categoryIds !== []) {
                $linkStmt = $pdo->prepare('INSERT IGNORE INTO category_messages (category_id, message_id) VALUES (:category_id, :message_id)');
                foreach ($categoryIds as $categoryId) {
                    $linkStmt->execute([
                        'category_id' => $categoryId,
                        'message_id' => $messageId,
                    ]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    redirect('messages.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$messages = $pdo->query(
    'SELECT m.id, m.title, m.content, m.created_at,
        COALESCE(GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", "), "Sem categoria") AS categories
     FROM messages m
     LEFT JOIN category_messages cm ON cm.message_id = m.id
     LEFT JOIN categories c ON c.id = cm.category_id
     GROUP BY m.id
     ORDER BY m.id DESC'
)->fetchAll();

require __DIR__ . '/header.php';
?>
<main>
    <section class="card">
        <h2>Cadastrar mensagem</h2>
        <form method="post">
            <label for="title">Título curto</label>
            <input id="title" name="title" required>

            <label for="content">Frase / mensagem</label>
            <textarea id="content" name="content" rows="4" required></textarea>

            <fieldset>
                <legend>Categorias da mensagem</legend>
                <div class="checks">
                    <?php foreach ($categories as $category): ?>
                        <label>
                            <input type="checkbox" name="category_ids[]" value="<?= (int) $category['id'] ?>">
                            <?= h($category['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <button type="submit">Salvar mensagem</button>
        </form>
    </section>

    <section class="card">
        <h2>Mensagens cadastradas</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Mensagem</th>
                <th>Categorias</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= (int) $message['id'] ?></td>
                    <td><?= h($message['title']) ?></td>
                    <td><?= nl2br(h($message['content'])) ?></td>
                    <td><?= h($message['categories']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
