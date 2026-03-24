<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$products = $pdo->query('SELECT id, name, description FROM products ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedProductId = (int) ($_POST['product_id'] ?? 0);
    if ($postedProductId > 0) {
        $_SESSION['live_product_id'] = $postedProductId;
        redirect('live.php?product_id=' . $postedProductId);
    }
}

if (isset($_GET['product_id'])) {
    $queryProductId = (int) $_GET['product_id'];
    if ($queryProductId > 0) {
        $_SESSION['live_product_id'] = $queryProductId;
    }
}

$selectedProductId = (int) ($_SESSION['live_product_id'] ?? ($products[0]['id'] ?? 0));
$selectedProduct = null;
foreach ($products as $product) {
    if ((int) $product['id'] === $selectedProductId) {
        $selectedProduct = $product;
        break;
    }
}

$messages = [];
if ($selectedProductId > 0) {
    $stmt = $pdo->prepare(
        'SELECT DISTINCT m.id, m.title, m.content
         FROM messages m
         INNER JOIN category_messages cm ON cm.message_id = m.id
         INNER JOIN product_categories pc ON pc.category_id = cm.category_id
         WHERE pc.product_id = :product_id
         ORDER BY RAND()'
    );
    $stmt->execute(['product_id' => $selectedProductId]);
    $messages = $stmt->fetchAll();
}

require __DIR__ . '/header.php';
?>
<main>
    <section class="card">
        <h2>Tela de apresentação</h2>
        <form method="post" class="inline-form">
            <label for="product_id">Produto</label>
            <select id="product_id" name="product_id">
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" <?= (int) $product['id'] === $selectedProductId ? 'selected' : '' ?>>
                        <?= h($product['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Carregar frases</button>
            <?php if ($selectedProductId > 0): ?>
                <a class="button-link" href="live.php?product_id=<?= $selectedProductId ?>">Sortear novamente</a>
            <?php endif; ?>
        </form>
    </section>

    <?php if ($selectedProduct): ?>
        <section class="card">
            <h2><?= h($selectedProduct['name']) ?></h2>
            <p><?= nl2br(h($selectedProduct['description'])) ?></p>
        </section>
    <?php endif; ?>

    <section class="card">
        <h3>Frases relacionadas às categorias do produto</h3>
        <?php if ($messages === []): ?>
            <p>Nenhuma frase encontrada para este produto. Verifique as associações de categorias e mensagens.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($messages as $message): ?>
                    <article class="phrase">
                        <h4><?= h($message['title']) ?></h4>
                        <p><?= nl2br(h($message['content'])) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
